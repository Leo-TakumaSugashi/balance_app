<?php
function check_null($data, $key, $default_value) {    
    $var = isset($data[$key]) ? $data[$key] : $default_value;
    if (is_null($var)) {
        return $default_value;
    } else {
        return $var;
    }
}

session_start();
$data = json_decode(file_get_contents("php://input"), true);
$CodeDisplay = FALSE;

// file_put_contents("debug_log.txt", "[process.php :" . date('H:i:s') . " ]" . print_r($data, true), FILE_APPEND);

// if (isset($data['ids'])) {
    // $_SESSION['selected_ids'] = $data['ids'];
// }
date_default_timezone_set('Asia/Tokyo');
echo "<h2><u>" . date('Y-m-d (l)') . "&nbsp;" ;
echo "<span style='color:rgb(200,0,170);font-weight:Bold; font-size:1.5rem; '><i>" . date('H:i:s') . "</u></i></span></h2><hr>";



$default_selected_sheet = "all";
$default_credit         = "Disable";
$default_creditRealDay  = "False";
$default_startDate      = "2025-02-01";
$default_endDate        = "2030-12-31";


$selected_sheet = check_null($data, "selected_sheet", $default_selected_sheet);
$credit         = check_null($data, "credit_visible", $default_credit        );
$credit_realday = check_null($data, "credit_realday", $default_creditRealDay );
$startDate      = check_null($data, "startDate"     , $default_startDate     );
$endDate        = check_null($data, "endDate"       , $default_endDate       );

$py1 = "python3 calc_balance.py --viewtype '$selected_sheet' --credit '$credit' --creditrealday ' $credit_realday ' --duration '$startDate to $endDate'";
if ( $CodeDisplay ) {
    echo "<p>$py1</p>";
}

$transactions = shell_exec($py1);

$file_path = './CSS/Category.txt';
$BGcolors = [];
$Fontcolors = [];

if (file_exists($file_path)) {            
    $lines = file($file_path, FILE_IGNORE_NEW_LINES);
    foreach ($lines as $line) {                
        list($item, $rgb, $fontColor) = array_pad(explode(', ', $line), 3, '(255,255,255)');
        $BGcolors[$item] = "rgb$rgb";
        $Fontcolors[$item] = "rgb$fontColor";
    }
} else {
    echo "<h3>Color.txt is not Found!!!!!</h3>";
}

// print_r($transactions);
// 
$transactions = json_decode($transactions, true);
if (json_last_error() != JSON_ERROR_NONE) {
    echo "<h3><i>JSONデコードエラー: <u>" . json_last_error_msg() . "</u></i></h3>";
// } else {
    // print_r($data); 
}

// <th>日付</th>
// <th>種類</th>
// <th>項目</th>
// <th>金額</th>
// <th>累計</th>
// <th>備考</th>

echo <<<HTML
<table border="1">
<tr style='background-color: rgb(221, 221, 221)'>
<th>Date</th>
<th>Type</th>
<th>METHOD</th>
<th>Category</th>
<th>Amount</th>
<th>Total</th>
<th>Remarks</th>
<th>Payment-date</th>
</tr>
HTML;

foreach ($transactions as $part) {                 
    if (isset($BGcolors[(string)$part['category']])) {
        $bg_color = $BGcolors[(string)$part['category']];
        $font_color = $Fontcolors[(string)$part['category']];
    } else {
        $bg_color = "rgb(255, 255, 255)"; // 色がない場合は白
    }
    // print_r($part);
    // print_r($part['date']);
    // print_r($part['creditdate']);
    // echo  "<br>";
    // continue;
    $class = ((string)$part['type'] == "支出") ? "minus_cell" : "plus_cell";                      
    echo "<tr>";
    echo "<td class='$class' style='font-size:0.7rem;'>" . htmlspecialchars($part['date']) . "</td>";
    echo "<td class='$class' style='font-size:0.7rem;'>" . htmlspecialchars($part['type']) . "</td>";
    echo "<td class='$class' style='font-size:0.7rem;'>" . htmlspecialchars($part['method']) . "</td>";

    $clen = strlen($part['category']);
    if ($clen > 12) {
        $fsiz='0.7rem' ;
    } else {
        $fsiz = '1rem' ;
    }
    echo "<td style='background-color: {$bg_color}; color: {$font_color}; font-size: $fsiz'>" . htmlspecialchars($part['category']) . "</td>";
    if ($part['amount'] < 0) {
        $fcolor="rgb(255,100,100)";
        echo "<td class='$class' style='color: {$fcolor}; font-style:bold;'>" .
         "¥" . number_format($part['amount']) . "</td>";
    } else {
        echo "<td class='$class'>" . "¥" . number_format($part['amount']) . "</td>";
    }
    if ($part['cumsum'] < 0) {
        $fcolor="rgb(255,100,100)";
        echo "<td class='$class' style='color: {$fcolor}; font-style:bold;'>" . 
        "<span style='font-style:normal;'>&#x25B2;</span>¥" . number_format($part['cumsum']) . "</td>";
    } else {
        echo "<td class='$class'>" . "¥" . number_format($part['cumsum']) . "</td>";
    }
  
    echo "<td class='$class' style='font-size:0.7rem;'>" . htmlspecialchars($part['note']) . "</td>";
    echo "<td class='$class' style='font-size:0.8rem;'>" . htmlspecialchars($part['creditdate']) . "</td>";
    echo "</tr>";
}
echo "</table>";

// echo "<p>";
// print_r($transactions);
// echo "</p>";
echo "<br><br><br><h2>Subtotal by category</h2>";
$py2 = "python3 calc_balance.py --viewtype '$selected_sheet' --credit '$credit' --creditrealday '$credit_realday' --duration '$startDate to $endDate' --output 'JSON-Category'";
if ( $CodeDisplay ) {
    echo "<p>$py2</p>";
}
$transactions2 = shell_exec($py2);
$transactions2 = json_decode($transactions2, true);
if (json_last_error() != JSON_ERROR_NONE) {
    echo "<h3><i>JSONデコードエラー: <u>" . json_last_error_msg() . "</u></i></h3>";
// } else {
    // print_r($data); 
}
echo <<<HTML
<table border="1" style="font-size:1.3rem;">
<tr>
    <th>Category</th>
    <th>Sub-total</th>
</tr>
HTML;
foreach ($transactions2 as $part) {                 
    if (isset($BGcolors[(string)$part['category']])) {
        $bg_color = $BGcolors[(string)$part['category']];
        $font_color = $Fontcolors[(string)$part['category']];
    } else {
        $bg_color = "rgb(200, 200, 200)"; 
        $font_color = "rgb(255, 255, 255)"; // 色がない場合は白
    }

    $class = "plus_cell";                      
    echo "<tr>";
    $fsiz = '1.4rem' ;
    if ($part['category'] == "合計") {
        $specialstyle = "style='background-color: rgb(198, 198, 198); color: black; font-size: 2rem'";
    } else {
        $specialstyle = "style='background-color: {$bg_color}; color: {$font_color}; font-size: $fsiz'";                
    }
    echo "<td $specialstyle>" . htmlspecialchars($part['category']) . "</td>";        
    echo "<td $specialstyle>" . "¥" . number_format($part['SumAmount']) . "</td>";
    echo "</tr>";
}
echo "</table><br><br><br><br>";

$py3 = "python3 calc_balance.py --viewtype 'all' --duration '2025-01-01 to' --output 'Current-Sumation'";
if ( $CodeDisplay ) {
    echo "<p>$py3</p>";
}

$transactions3 = shell_exec($py3);
$SumTable = json_decode($transactions3, true);
echo "<h1> <u><i>Current total amount: " . $SumTable['sum'] . "</i></u>";
echo "<br> <span style='font-size:1.2rem;'>breakdown</span> - BANK: " . $SumTable['bank'] ;
echo "<br> <span style='font-size:1.2rem;'>breakdown</span> - CASH: " . $SumTable['cash'] . "</h1>";

        
echo '<script>console.log(' . json_encode($transactions3) . ');</script>';

?>
