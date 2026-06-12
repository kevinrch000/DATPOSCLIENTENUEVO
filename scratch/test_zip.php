<?php
class SimpleZip {
    private $files = array();

    public function addFile($filename, $data) {
        $this->files[] = array(
            'name' => $filename,
            'data' => $data
        );
    }

    public function getZipData() {
        $zipData = '';
        $cdData = '';
        $offset = 0;

        foreach ($this->files as $file) {
            $name = $file['name'];
            $data = $file['data'];
            $len = strlen($data);
            $crc = crc32($data);
            $nameLen = strlen($name);

            // Local File Header
            $lfh = pack('VvvvvvVVVvv', 
                0x04034b50, // signature
                10,         // version needed
                0,          // flags
                0,          // compression method (Store)
                0,          // last mod time
                0,          // last mod date
                $crc,       // crc32
                $len,       // compressed size
                $len,       // uncompressed size
                $nameLen,   // filename length
                0           // extra field length
            ) . $name;
            
            $zipData .= $lfh . $data;

            // Central Directory File Header
            $cdfh = pack('VvvvvvvVVVvvvvvVV', 
                0x02014b50, // signature
                20,         // version made by
                10,         // version needed
                0,          // flags
                0,          // compression method
                0,          // last mod time
                0,          // last mod date
                $crc,       // crc32
                $len,       // compressed size
                $len,       // uncompressed size
                $nameLen,   // filename length
                0,          // extra field length
                0,          // comment length
                0,          // disk number start
                0,          // internal file attrs
                0,          // external file attrs
                $offset     // local header offset
            ) . $name;

            $cdData .= $cdfh;
            $offset += strlen($lfh) + $len;
        }

        $cdLen = strlen($cdData);
        
        // End of Central Directory
        $eocd = pack('VvvvvVVv', 
            0x06054b50,         // signature
            0,                  // disk number
            0,                  // disk with CD start
            count($this->files),// num CD records on disk
            count($this->files),// total num CD records
            $cdLen,             // size of CD
            $offset,            // offset of CD
            0                   // comment length
        );

        return $zipData . $cdData . $eocd;
    }
}

$zip = new SimpleZip();
$zip->addFile("test.txt", "Hello world from pure PHP ZIP!");
$data = $zip->getZipData();
file_put_contents("scratch/test_output.zip", $data);
echo "ZIP generated. Size: " . strlen($data) . " bytes\n";
?>
