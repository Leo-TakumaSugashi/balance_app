
<?php
session_start();
// CSVファイルのパス
$csv_file = './CSV/transactions.csv';

// Pythonスクリプトを実行して月ごとのバランスを取得
$python_script = 'python3 calculate_balance.py';
$output = shell_exec($python_script);
$monthly_balance = json_decode($output, true);

$userAgent = $_SERVER['HTTP_USER_AGENT'];

$fontSize = "18px";  
if (strpos($userAgent, 'Mobile') !== false) {
    $fontSize = "9px";
} 
// Windows
elseif (strpos($userAgent, 'Windows NT') !== false) {
    $fontSize = "18px";
} 
// Mac
elseif (strpos($userAgent, 'Macintosh') !== false) {
    $fontSize = "18px";
} 
// Safari
elseif (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
    // Safariでは、Chromeを含まない「Safari」の文字列で判定
    $fontSize = "20px";
} 
// Chrome
elseif (strpos($userAgent, 'Chrome') !== false) {
    $fontSize = "20px";
}


setlocale(LC_TIME, 'ja_JP.UTF-8');
$current_dateB = new DateTime();
$current_dateB = $current_dateB->format('Y-m-d');
$current_date = new DateTime();
$current_date->modify('+1 month');
$current_date->setDate($current_date->format('Y'), $current_date->format('m'), 15);
$current_date->format('Y-m-d');  // 例: 2025-04-15
$current_date = $current_date->format('Y-m-d');
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>家計簿 - 表示</title>
    <link rel="stylesheet" href="./CSS/viewstyle.css">
    <style>
        body {
            font-size: <?php echo $fontSize; ?>;
        }
    </style>
    <script src="./CSS/viewstyle.js"></script>
</head>
<body>
    <p style='padding-left: 10px'><a href="./add.php"> Add Data.</a></p>
    <hr>
    <!-- <p><?php echo $current_date; ?></p> -->
    <h1 style="margin-left: 50pt;">家計簿一覧</h1>
    <div class="controller-panel">
        <label>Duration : </label>
        <!-- <label>Start <input type="date" name="startdate" value="2025-02-01" required></label> -->
        <label>Start <input type="date" name="startdate" value="<?php echo $current_dateB; ?>" required></label>
        <label>End <input   type="date" name="enddate"  value="<?php echo $current_date; ?>"  required></label>
        <br>
        <label> Credit Visible : </label>
        <label class="switch" >
            <input type="checkbox" id="credit_visible">
            <span class="slider round"></span>
        </label>
        <br>
        <label> Credit Real-Day : </label>
        <label class="switch">
            <input type="checkbox"  id="credit_realday">
            <span class="slider round"></span>
        </label>
        <br>
        <label> Select Sheet: 
            <select name="viewtype" required>
                <option value="all">ALL</option>                                                      
                <?php
                    $cmd = "find ./CSV/ -type f -name '*.csv' | sed 's/\.\/CSV\///'";
                    $results = shell_exec($cmd);
                    $files = explode("\n", $results);
                    foreach ( $files as $f) {
                        if (!empty($f)) {
                            echo "<option value=\"$f\">$f</option>";
                        }
                    }
                ?>             
            </select>
        </label><br>
        <!-- <button id="duration_apply" onclick="set_option()">Apply</button> -->
        <button id="duration_apply" onclick="updateTable()">Apply</button>
        
    </div>

    <div class="center-panel" id="main-panel"> 
        <?php include './updatetable.php'; ?> 
    </div>
    <h2>月ごとのバランス</h2>
    <table border="1">
        <tr>
            <th>月</th>
            <th>収支</th>
        </tr>
        <?php if ($monthly_balance): ?>
            <?php foreach ($monthly_balance as $month => $balance): ?>
                <tr>
                    <td><?php echo htmlspecialchars($month); ?></td>
                    <td><?php echo htmlspecialchars(number_format($balance)); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="2">データなし</td></tr>
        <?php endif; ?>
    </table>
</body>
<script>
    window.onload = toggleCreditVisible;
    document.getElementById('credit_visible').addEventListener('change', toggleCreditVisible);
</script>
</html>
