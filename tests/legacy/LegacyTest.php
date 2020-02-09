<?php

class LegacyUnitTests extends AbstractTest
{
    public function dataProvider_legacy_unit_tests()
    {
        $files = scandir(__DIR__);
        $legacy_tests = [];
        foreach ($files as $file) {
            if (preg_match("/\.phpt$/", $file)) {
                $test_parts = file_get_contents(__DIR__ . '/' . $file);
                $test_parts = preg_split("/(^|\n)--/", $test_parts);
                $parts = [];
                foreach (array_filter($test_parts) as $test_part) {
                    if (! preg_match("#^(\w+)--(\n([\s\S]+))?$#m", $test_part, $matches)) {
                        throw new \Exception('Invalid test part: '.$test_part);
                    }
                    $parts[$matches[1]] = isset($matches[3]) ? $matches[3] : '';
                }

                $legacy_tests[$file] = $parts;
            }
        }

        $out = [];
        foreach ($legacy_tests as $filename => $legacy_test) {
            $out[] = [$filename, $legacy_test];
        }

        return $out;
    }

    /**
     * @dataProvider dataProvider_legacy_unit_tests
     */
    public function test_legacy_unit_tests($filename, $parts)
    {
        $php_version = explode('.',phpversion());
        $php_version = $php_version[0] . '.' . $php_version[1];
        
        $current_test_filename = __DIR__ . '/current_test.php';
        file_exists($current_test_filename) && unlink($current_test_filename);
        file_put_contents($current_test_filename, $parts['FILE']);
        $output = shell_exec("php$php_version $current_test_filename");

        $this->assertEquals(
            trim($parts['EXPECT']),
            trim($output)
        );

        if (isset($parts['CLEAN'])) {
            $current_clean_filename = __DIR__ . '/current_clean.php';
            file_put_contents($current_clean_filename, $parts['CLEAN']);
            require_once $current_clean_filename;
            unlink($current_clean_filename);
        }
    }

    /**/
}
