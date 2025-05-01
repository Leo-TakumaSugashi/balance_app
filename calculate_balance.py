import pandas as pd
import json

# CSVファイルを読み込む
df = pd.read_csv('./CSV/transactions.csv', names=['date', 'type', 'category', 'amount', 'note', 'creditdate'])

# 日付をdatetime型に変換
df['date'] = pd.to_datetime(df['date'], errors='coerce')

# 金額を数値に変換
df['amount'] = pd.to_numeric(df['amount'], errors='coerce')

# 収入を正の値、支出を負の値にする
df['amount'] = df.apply(lambda row: row['amount'] if row['type'] == '収入' else -row['amount'], axis=1)

# 月ごとのバランスを計算
monthly_balance = df.groupby(df['date'].dt.to_period('M'))['amount'].sum()
# print(monthly_balance)
output = {str(month): balance for month, balance in monthly_balance.items()}

# for month, balance in monthly_balance.items():
    # print(f"{str(month)}: {balance }")

# JSONで出力
# print(json.dumps(output.to_dict(), ensure_ascii=False))
print(json.dumps(output, ensure_ascii=False))
