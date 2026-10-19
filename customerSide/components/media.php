<?php
// media.php - small helper functions used by the pages (pictures, numbers, dish notes)


// makes a word clean for comparing: small letters, no symbols, no word "delicious"
function ember_clean_text($text) {
    $text = strtolower((string) $text);
    $text = str_replace(['&', "'", '’'], [' and ', '', ''], $text);
    $text = preg_replace('/\bdelicious\b/', ' ', $text);
    $text = preg_replace('/[^a-z0-9]+/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}


// returns the dish description to show under the dish name
// it returns '' (nothing) if the description only repeats the name, like "delicious chicken 65"
function ember_dish_note($description, $dishName, $courseName = '') {
    $note = ember_clean_text($description);
    if ($note == '') {
        return '';
    }

    $name = ember_clean_text($dishName);
    $course = ember_clean_text($courseName);

    // description is already inside the dish name -> don't show it
    if ($name != '' && strpos($name, $note) !== false) {
        return '';
    }
    // description is already inside the course name -> don't show it
    if ($course != '' && strpos($course, $note) !== false) {
        return '';
    }

    // remove the word "delicious" and show the rest
    $result = preg_replace('/\bdelicious\b/i', '', (string) $description);
    $result = trim(preg_replace('/\s+/', ' ', $result));
    if ($result == '') {
        return '';
    }
    return ucfirst($result);
}


// turns a number into a word, e.g. 9 -> "nine" (numbers above 20 stay as digits)
function ember_word($number) {
    $words = [
        1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five',
        6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten',
        11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
        16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen', 20 => 'twenty',
    ];
    if (isset($words[$number])) {
        return $words[$number];
    }
    return (string) $number;
}


// prints a photo from image/editorial/
// every photo has 4 files: big and small (640px), in jpg and webp
// $sizes tells the browser how wide the photo will be on the screen
function ember_picture($name, $alt, $sizes) {
    $big = '../image/editorial/' . $name;
    $small = '../image/editorial/' . $name . '@640';

    // ?v=... changes when the file changes, so the browser never shows an old copy
    $version = @filemtime(__DIR__ . '/../image/editorial/' . $name . '.jpg');
    if (!$version) {
        $version = '1';
    }
    $v = '?v=' . $version;

    $sizes = htmlspecialchars($sizes, ENT_QUOTES);
    $alt = htmlspecialchars($alt, ENT_QUOTES);
    ?>
        <picture>
            <source type="image/webp"
                    srcset="<?= $small ?>.webp<?= $v ?> 640w, <?= $big ?>.webp<?= $v ?> 1920w"
                    sizes="<?= $sizes ?>">
            <img src="<?= $big ?>.jpg<?= $v ?>"
                 srcset="<?= $small ?>.jpg<?= $v ?> 640w, <?= $big ?>.jpg<?= $v ?> 1920w"
                 sizes="<?= $sizes ?>"
                 alt="<?= $alt ?>"
                 width="1920" height="1280"
                 loading="lazy"
                 decoding="async">
        </picture>
    <?php
}
