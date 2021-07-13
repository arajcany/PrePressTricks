<?php


namespace arajcany\PrePressTricks\Graphics\ImageMagick;


class IdentifyParser
{
    private $data = [];
    private $line_number = 0;

    public function parse($content)
    {
        if (is_array($content)) {
            $lines = $content;
        } else if (is_resource($content)) {
            $lines = explode("\n", stream_get_contents($content));
        } else {
            $lines = explode("\n", $content);
        }

        $raw_data = $this->doParse($lines);
        $this->data = $raw_data['Image'];
        $this->line_number = 0;

        return $this;
    }

    public function toJson($flags = null)
    {
        return json_encode($this->data, $flags);
    }

    public function __toString()
    {
        return $this->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function toArray()
    {
        return $this->data;
    }

    public function __get($key)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : false;
    }

    private function doParse($lines, $my_depth = 0)
    {

        $data = [];

        while (true) {

            if ($this->line_number >= count($lines)) {
                break;
            }

            $line = rtrim($lines[$this->line_number++]);

            if (strlen($line) === 0) {
                continue;
            }

            if (!preg_match('/^( *)\b(.+?): *(.*)$/', $line, $matches)) {
                die("Unable to parse line:\n'$line'\n");
            }

            list($match, $indent, $key, $value) = $matches;
            $key = trim($key);
            $value = trim($value);

            $current_depth = strlen($indent) / 2;
            if ($current_depth > 0 && $current_depth <= $my_depth) {
                $this->line_number--;
                return $data;
            }

            if (strlen($value) === 0 || $key == "Image") {
                $subdata = $this->doParse($lines, $current_depth);
                $data[$key] = $subdata;

                if ($key == "Image") {
                    $data[$key] = ['Image' => $value] + $data[$key];
                }

            } else {
                $hasHexColour = preg_match('/#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})/', $value) ? true : false;

                //cater for special conditions
                if (substr_count($line, ':') > 1 && substr_count($line, ': ') == 1) {
                    //checks for multiple : delimiters such as in "tiff:timestamp: 2021:07:13 07:27:41"
                    $tmp = explode(": ", $line);
                    $value = array_pop($tmp);
                    $value = trim($value);
                    $key = implode(": ", $tmp);
                    $key = trim($key);
                } elseif (is_numeric($key) && $hasHexColour) {
                    //checks if the value contains and hex colour
                    $keyTmp = $value;
                    $valueTmp = $key + 0;
                    $key = $keyTmp;
                    $value = $valueTmp;
                }

                $data[$key] = $value;
            }
        }

        return $data;
    }
}