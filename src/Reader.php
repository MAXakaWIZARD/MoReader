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
     * @var string
     */
    protected $filename;

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
        $this->openFile($filename);

        $data = array();

        $this->determineByteOrder();
        $this->verifyMajorRevision();

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

            $originalString = array('');
            if ($originalStringSize > 0) {
                fseek($this->file, $originalStringOffset);
                $originalString = explode("\0", fread($this->file, $originalStringSize));
            }

            if ($translationStringSize > 0) {
                fseek($this->file, $translationStringOffset);
                $translationString = explode("\0", fread($this->file, $translationStringSize));

                if (count($originalString) > 1 && count($translationString) > 1) {
                    $data[$originalString[0]] = $translationString;

                    array_shift($originalString);

                    foreach ($originalString as $string) {
                        $data[$string] = '';
                    }
                } else {
                    $data[$originalString[0]] = $translationString[0];
                }
            }
        }

        fclose($this->file);

        return $data;
    }

    /**
     * Prepare file for reading
     *
     * @param $filename
     *
     * @throws \Exception
     */
    protected function openFile($filename)
    {
        $this->filename = $filename;

        if (!is_file($this->filename) || !is_readable($this->filename)) {
            throw new \Exception(
                sprintf(
                    'Could not open file %s for reading',
                    $this->filename
                )
            );
        }

        $this->file = fopen($this->filename, 'rb');
        if (false === $this->file) {
            throw new \Exception(
                sprintf(
                    'Could not open file %s for reading',
                    $this->filename
                ),
                0
            );
        }
    }

    /**
     * Determines byte order
     *
     * @throws \Exception
     */
    protected function determineByteOrder()
    {
        $orderHeader = fread($this->file, 4);

        if ($orderHeader == "\x95\x04\x12\xde") {
            $this->littleEndian = false;
        } elseif ($orderHeader == "\xde\x12\x04\x95") {
            $this->littleEndian = true;
        } else {
            fclose($this->file);
            throw new \Exception(
                sprintf(
                    '%s is not a valid gettext file',
                    $this->filename
                )
            );
        }
    }

    /**
     * Verify major revision (only 0 and 1 supported)
     *
     * @throws \Exception
     */
    protected function verifyMajorRevision()
    {
        $majorRevision = ($this->readInteger() >> 16);

        if ($majorRevision !== 0 && $majorRevision !== 1) {
            fclose($this->file);
            throw new \Exception(
                sprintf(
                    '%s has an unknown major revision',
                    $this->filename
                )
            );
        }
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
