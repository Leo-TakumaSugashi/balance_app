.
├── CSS
│   ├── Category.txt
│   ├── creditNote2category.list
│   ├── viewstyle.css
│   └── viewstyle.js
├── CSV
│   ├── 2025_03_Dcard_classified.csv
│   ├── ...
│   └── transactions.csv
├── add.php    -- For Credit: Pre_Dcard_classified.csv / For Normal: transactions.csv
├── view.php   -- Almost complete. Please use, improve, and debug.
├── updatetable.php    -- Displays the main panel.
├── calc_balance.py    -- Calculates CSV data and displays main panel.
├── read_dcard_detail.py  -- Submodule used by calc_balance.py
├── calculate_balance.py  -- Submodule for displaying balance summary.
├── ReadMe.txt  --- It's me!!
└── debug_log.txt

📄 transactions.csv
This CSV file should follow the column order:
date, type, method, category, amount, note, creditdate

Lines starting with # are treated as comments and will be ignored during parsing.


📄 Category.txt
Each line should be formatted as follows:
CategoryName, (R,G,B), (R,G,B)

・The first value is the category name.
・The second is the background color (RGB).
・The third is the text color (RGB).
・Values must be separated by a comma and a space: ", "

## Requirements 
python3
  pandas
  numpy

## Basic usage and execution
php -S localhost:10099  ./ -t view.php
*Port number 10099 is available for use on your PC/server.

## If you have any questions or issues, please contact:
Leo-Takuma SUGASHI (oshou.0131@gmail.com)
