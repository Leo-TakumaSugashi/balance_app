<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function adj_pay_date($input) {
    setlocale(LC_TIME, 'en_US.UTF-8');
    $current_date = new DateTime($input);
    // 現在の日が1日から15日か16日以降かをチェック
    if ($current_date->format('j') <= 15) {
        // 1日から15日の場合、来月の11日を計算
        $current_date->modify('first day of next month');
        $current_date->setDate($current_date->format('Y'), $current_date->format('m'), 11);
    } else {
        // 16日以降の場合、再来月の11日を計算
        $current_date->modify('first day of next month');
        $current_date->modify('+1 month');
        $current_date->setDate($current_date->format('Y'), $current_date->format('m'), 11);
    }
    $adjdate = $current_date;

    $day_of_week = $adjdate->format('l');  // 'l' で曜日名を取得（例: Sunday, Monday）
    if ($day_of_week == 'Sunday') {
        $adjdate->modify('-2 days');
    }
    else if ($day_of_week == 'Saturday') {
        $adjdate->modify('-1 day');
    }

    setlocale(LC_TIME, '');
    return $adjdate->format('Y-m-d');
}

function remove_newlines($str) {
    $str = str_replace(array("\r\n", "\r", "\n"), '', $str);
    return $str;
}

setlocale(LC_TIME, 'ja_JP.UTF-8');
date_default_timezone_set('Asia/Tokyo');
$current_date = date('Y-m-d');

// CSVファイルのパス
$csv_file_normal = './CSV/transactions.csv';
$csv_file_creditpre = './CSV/Pre_Dcard_classfied.csv';

$category_file = './CSS/Category.txt';

$categories = shell_exec('cat ' . $category_file . "| awk -F',' '{print $1}'");
$categories = explode("\n", trim($categories));
$categories = array_filter($categories, function($value) {
    return !empty($value);
});


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? '';
    $type = $_POST['type'] ?? '';
    $method = $_POST['method'] ?? '';
    $category = $_POST['category'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $note = $_POST['note'] ?? '';
    $note = remove_newlines($note);
    

    
    if ($type === "Bank to Cash" || $type === "Cash to Bank") {
        $ntype = $type === "Bank to Cash" ? ["支出", "収入"] : ["収入", "支出"];
        $ncategory = $type === "Bank to Cash" ? ["BANK", "CASH"] : ["BANK", "CASH"];
        $entry1 = [$date, $ntype[0], $ncategory[0], $method, $amount, $note, "",];
        $entry2 = [$date, $ntype[1], $ncategory[1], $method, $amount, $note, "",];
        $fp = fopen($csv_file_normal, 'a');
        fputcsv($fp, $entry1);
        fputcsv($fp, $entry2);
        fclose($fp);


    } elseif ($date && $type && $category) {
        if ($method === "Credit") {
            $csv_file = $csv_file_creditpre;
            $credit_pay_date = adj_pay_date($date);
            $entry = [$credit_pay_date, $type, $method, $category, $amount, $note, $date];
        } else { 
            $csv_file = $csv_file_normal;
            $credit_pay_date = "";
            $entry = [$date, $type, $method, $category, $amount, $note, $credit_pay_date];
        }
                   
        // echo '<script>console.log(' . json_encode($entry) . ');</script>';
 
        $fp = fopen($csv_file, 'a');
        fputcsv($fp, $entry);
        fclose($fp);


        // if ($result === false) {
            // echo '<script>console.log("Failed to write to CSV.");</script>';
        // } else {
            // echo '<script>console.log("Successfully wrote ' . $result . ' fields to CSV.");</script>';
        // }
        


    } 
    // header("Refresh: 3; url=./view.php");
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>家計簿 - 追加</title>
    <link rel="stylesheet" href="./CSS/viewstyle.css">
    <style>
        body { font-size: 18px;}
        .center-panel {
            display: flex;
            justify-content: center;
            min-width: 200px;
            min-height: 200px;                   
            align-items: center;
        }
        input, select, option, button { 
            font-size:24px;            
            margin: 5px, 115px, 15px, 5px;
        }
        button {            
            font-size: 28px;
            margin-left: 100px;
        }
        .harfhr {
            height:3pt;
            visibility:hidden;
        }
    </style>
    <script src="./CSS/viewstyle.js"></script>
</head>
<body>
    <p style='padding-left: 20px'><a href="./view.php"> View Data.</a></p>
    <p style='text-align: right'><?php echo date('Y-m-d(l) H:i:s') ?></p>
    <hr>
    <h1>Balance -INPUT-</h1>    
    <div class="center-panel">
    <form action="add.php" method="post">
        <label>Date: <input type="date" name="date" value="<?php setlocale(LC_ALL, 'ja_JP.UTF-8'); echo $current_date; ?>" required></label><br>
        <hr class="harfhr" >
        <label>Type: 
            <select name="type" id="type" required>
                <option value="" selected disable hidden>PLESE SELECT</option>                
                <option value="支出">支出</option>
                <option value="収入">収入</option>
                <option value="Bank to Cash">Bank to Cash</option>
                <option value="Cash to Bank">Cash to Bank</option>
            </select>
        </label><br>
        <hr class="harfhr" >
        <label> Method : 
            <select name="method" id="method" required>                
                <!-- <option value="Credit">Credit</option> -->
                <!-- <option value="CASH">CASH</option> -->
                <!-- <option value="BANK">BANK</option> -->
            </select>
        </label>
        <!-- <label class="switch" > -->
            <!-- <input type="checkbox" id="credit_use" name="credit_use" checked > -->
            <!-- <span class="slider round"></span> -->
        <!-- </label> -->
        <br>
        <hr class="harfhr" >
        <label>Category: 
            <select name="category" id="category" required>
                <!-- <?php foreach ($categories as $cat): ?> -->
                    <!-- <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option> -->
                <!-- <?php endforeach; ?> -->
            </select>
        </label><br>
        <hr class="harfhr" >
        <label>Amount: <input type="number" name="amount" required></label><br>
        <hr class="harfhr" >
        <label>Note: <textarea name="note"></textarea></label><br>
        <hr class="harfhr" >
        <button type="submit">ADD</button>
    </form>
    </div>

</body>
<script>
    document.getElementById('type').addEventListener('change', type_Changed );

    // window.onload = toggleCreditUse;
    // document.getElementById('type').addEventListener('change', toggleCreditUse);
</script>

</html>

