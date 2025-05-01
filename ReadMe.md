# Project Overview

***This project contains several scripts and files for managing and processing financial data.***


### 📄 `transactions.csv`

This CSV file should follow the column order:
- `date`, `type`, `method`, `category`, `amount`, `note`, `creditdate`

Lines starting with `#` are treated as comments and will be ignored during parsing.

### 📄 `Category.txt`

Each line should be formatted as follows:
- `CategoryName, (R,G,B), (R,G,B)`

Where:
- The first value is the category name.
- The second is the background color (RGB).
- The third is the text color (RGB).
- Values must be separated by a comma and a space: `", "`.

## Requirements

- Python 3
- pandas
- numpy

## Basic Usage and Execution

To start the PHP server, use the following command:

```bash
php -S localhost:10099 -t ./ view.php
'''
**Note: Port number 10099 is available for use on your PC/server.**

## Contact Information
If you have any questions or issues, please contact:
- Leo-Takuma SUGASHI (oshou.0131@gmail.com)

