<?php

namespace MoReader;

/**
 * Gettext loader
 */
class Reader
{
    /**
     * Current file pointer.
     *
     * @var resource
     */
    protected $file;

    /**
     * Whether the current file is little endian.
     *
     * @var bool
     */
    protected $littleEndian;

    /**
     *
     * @param  string $filename
     * @return array
     * @throws \Exception
     */
    public function load($filename)
    {
        if (!is_file($filename) || !is_readable($filename)) {
            throw new \Exception(
                sprintf(
                    'Could not open file %s for reading',
                    $filename
                )
            );
        }

        $textDomain = [];

        $this->file = fopen($filename, 'rb');
        if (false === $this->file) {
            throw new \Exception(
                sprintf(
                    'Could not open file %s for reading',
                    $filename
                ),
                0
            );
        }

        // Verify magic number
        $magic = fread($this->file, 4);

        if ($magic == "\x95\x04\x12\xde") {
            $this->littleEndian = false;
        } elseif ($magic == "\xde\x12\x04\x95") {
            $this->littleEndian = true;
        } else {
            fclose($this->file);
            throw new \Exception(
                sprintf(
                    '%s is not a valid gettext file',
                    $filename
                )
            );
        }

        // Verify major revision (only 0 and 1 supported)
        $majorRevision = ($this->readInteger() >> 16);

        if ($majorRevision !== 0 && $majorRevision !== 1) {
            fclose($this->file);
            throw new \Exception(
                sprintf(
                    '%s has an unknown major revision',
                    $filename
                )
            );
        }

        // Gather main information
        $numStrings                   = $this->readInteger();
        $originalStringTableOffset    = $this->readInteger();
        $translationStringTableOffset = $this->readInteger();

        // Usually there follow size and offset of the hash table, but we have
        // no need for it, so we skip them.
        fseek($this->file, $originalStringTableOffset);
        $originalStringTable = $this->readIntegerList(2 * $numStrings);

        fseek($this->file, $translationStringTableOffset);
        $translationStringTable = $this->readIntegerList(2 * $numStrings);

        // Read in all translations
        for ($current = 0; $current < $numStrings; $current++) {
            $sizeKey                 = $current * 2 + 1;
            $offsetKey               = $current * 2 + 2;
            $originalStringSize      = $originalStringTable[$sizeKey];
            $originalStringOffset    = $originalStringTable[$offsetKey];
            $translationStringSize   = $translationStringTable[$sizeKey];
            $translationStringOffset = $translationStringTable[$offsetKey];

            $originalString = [''];
            if ($originalStringSize > 0) {
                fseek($this->file, $originalStringOffset);
                $originalString = explode("\0", fread($this->file, $originalStringSize));
            }

            if ($translationStringSize > 0) {
                fseek($this->file, $translationStringOffset);
                $translationString = explode("\0", fread($this->file, $translationStringSize));

                if (count($originalString) > 1 && count($translationString) > 1) {
                    $textDomain[$originalString[0]] = $translationString;

                    array_shift($originalString);

                    foreach ($originalString as $string) {
                        $textDomain[$string] = '';
                    }
                } else {
                    $textDomain[$originalString[0]] = $translationString[0];
                }
            }
        }

        fclose($this->file);

        return $textDomain;
    }

    /**
     * Read a single integer from the current file.
     *
     * @return integer
     */
    protected function readInteger()
    {
        if ($this->littleEndian) {
            $result = unpack('Vint', fread($this->file, 4));
        } else {
            $result = unpack('Nint', fread($this->file, 4));
        }

        return $result['int'];
    }

    /**
     * Read an integer from the current file.
     *
     * @param  integer $num
     * @return integer
     */
    protected function readIntegerList($num)
    {
        if ($this->littleEndian) {
            return unpack('V' . $num, fread($this->file, 4 * $num));
        }

        return unpack('N' . $num, fread($this->file, 4 * $num));
    }
}
