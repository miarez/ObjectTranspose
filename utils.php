<?php

# ...
function pp(
    $a,
    int     $exit   =0,
    string  $label  =''
) : void
{
    echo "<PRE>";
    if($label) echo "<h5>$label</h5>";
    if($label) echo "<title>$label</title>";
    echo "<pre>";
    print_r($a);
    echo '</pre>';
    if($exit) exit();
}

function array_to_table(
    array $array,
    string  $label  =''
): void
{
    $keymap = array_keys(reset($array));
    if($label) echo "<h5>$label</h5>";

    $output = "<thead><tr>\n";
    foreach ($keymap as $key) {
        $output .= "<th>" . htmlspecialchars($key) . "</th>\n";
    }
    $output .= "</tr></thead>\n\n";

    foreach ($array as $row) {
        $output .= "<tr class='dataline'>\n";
        foreach ($keymap as $key) {
            $value = $row[$key] ?? '';
            $output .= "<td><div>" . htmlspecialchars($value) . "</div></td>\n";
        }
        $output .= "</tr>\n\n";
    }

    echo "<table class='default_table'>$output</table>";
}

function read_json(
    string $file_path
) : array
{
    return json_decode(file_get_contents($file_path), true);
}


function read_csv(
    string $file_path,
    bool $headerAsKeys = true
): array
{
    $rows = [];
    $header = [];

    // Open the file for reading
    if (($handle = fopen($file_path, 'r')) !== false) {
        // Read the header (first line) if necessary
        if ($headerAsKeys && ($data = fgetcsv($handle)) !== false) {
            $header = $data;
        }

        // Read each line of the CSV
        while (($data = fgetcsv($handle)) !== false) {
            if ($headerAsKeys) {
                // Combine header and data if header is provided
                $rows[] = array_combine($header, $data);
            } else {
                // Just store the data array if no header is provided
                $rows[] = $data;
            }
        }
        fclose($handle);
    }
    return $rows;
}
