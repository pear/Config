<?php
use SebastianBergmann\Diff\Differ;

require_once __DIR__ . '/../vendor/autoload.php';

$files = scandir(__DIR__);

set_error_handler(function ($err_severity, $err_msg, $err_file, $err_line, array $err_context)
{
    throw new ErrorException( $err_msg, 0, $err_severity, $err_file, $err_line );
});
// }, E_WARNING);

$php_version = explode('.',phpversion());
$php_version = $php_version[0] . '.' . $php_version[1];

$arguments = [];
foreach ($argv as $i => $shell_argument) {
    if ($shell_argument == '--filter') {
        if (! empty($argv[$i+1])) {
            $arguments['filter'] = $argv[$i+1];
        }
    }
}
// var_dump($arguments);
// exit;

$differ = new Differ;

error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE);

$tests_failed = [];

foreach ($files as $file) {
    if (preg_match("/\.phpt$/", $file)) {

        if (! empty($arguments['filter']) && $file != $arguments['filter']) {
            continue;
        }

        $test_parts = file_get_contents(__DIR__ . '/' . $file);
        $test_parts = preg_split("/(^|\n)--/", $test_parts);
        $parts = [];
        foreach (array_filter($test_parts) as $test_part) {
            if (! preg_match("#^(\w+)--(\n([\s\S]+))?$#m", $test_part, $matches)) {
                throw new \Exception('Invalid test part: '.$test_part);
            }
            $parts[$matches[1]] = isset($matches[3]) ? $matches[3] : '';
        }

        echo "$file: {$parts['TEST']}\n";
        $current_test_filename = __DIR__ . '/current_test.php';
        file_exists($current_test_filename) && unlink($current_test_filename);
        file_put_contents($current_test_filename, $parts['FILE']);
        
        $output = shell_exec("php$php_version $current_test_filename");

        if (trim($output) != trim($parts['EXPECT'])) {
            echo "Test failed\n";

            // Options for generating the diff
            $options = array(
                //'ignoreWhitespace' => true,
                //'ignoreCase' => true,
            );

            // Initialize the diff class
            // print $differ->diff('foo', 'bar');
            $diff = new Diff(
                explode("\n", $parts['EXPECT']),
                explode("\n", $output),
                $options
            );
            
            $renderer = new Diff_Renderer_Text_Unified;
            echo $diff->render($renderer);
            echo "\n$output\n";
            // echo "Expected:\n{$parts['EXPECT']}\n";
            $tests_failed[] = "$file: {$parts['TEST']}";
            // exit;
        }
        
        if (isset($parts['CLEAN'])) {
            $current_clean_filename = __DIR__ . '/current_clean.php';
            file_put_contents($current_clean_filename, $parts['CLEAN']);
            require_once $current_clean_filename;
            unlink($current_clean_filename);
        }
    }
}
/**/
