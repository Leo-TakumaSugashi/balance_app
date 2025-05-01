import pandas as pd
import json
import os,sys,datetime
from read_dcard_detail import subFun_balance as subFun
import locale

try:
    locale.setlocale(locale.LC_ALL, 'ja_JP.UTF-8')  # Unix
except locale.Error:
    try:
        locale.setlocale(locale.LC_ALL, 'Japanese_Japan.932')  # Windows
    except locale.Error:
        localError = locale.Error

import argparse
parser = argparse.ArgumentParser(description="Process some options.")

# Add arguments
parser.add_argument("--viewtype", type=str, default="all", help="Specify the zone (default: 'all')")
parser.add_argument("--credit", type=str, default="enable", help="Enable or disable summary (default: 'enable')")
parser.add_argument("--creditrealday", type=str, default="enable", help="Credit payment day display(False -> 引き落とし日. True-> 使用日(但しCreditが全表示に限る。)) (default: 'False')")
parser.add_argument("--output", type=str, default="JSON", help="JSON, JSON-Category, and other is print(df) (default: 'JSON')")
parser.add_argument("--duration", type=str, default="all", help="all or duration[2000-01-01 to 2001-01-31] (default: 'all')")

# Parse the arguments
args = parser.parse_args()
# Access the arguments
viewtype = args.viewtype
credit = args.credit.replace(' ','')
credit_realday = args.creditrealday.replace(' ','')
output_type = args.output
duration = args.duration


ToDay = pd.Timestamp.today().date()

dayformat = '%Y-%m-%d (%a.)'

Option = sys.argv[1] if len(sys.argv) >1 else 'all'

def Read_csv(fname,creditRealDay=False,sortby='date'):
    
    if  creditRealDay :    
        df = pd.read_csv(fname,header=None, names=['creditdate', 'type', 'method', 'category', 'amount', 'note', 'date'])
        df = df[['date', 'type', 'method', 'category', 'amount', 'note', 'creditdate']]
    else:
        df = pd.read_csv(fname, names=['date', 'type', 'method', 'category', 'amount', 'note', 'creditdate'])
    ind = []
    for i, p in enumerate(df['date']):  
        if not(str(p).startswith("#")):  
            if not(str(p) is None):
                ind.append(i)
    df = df.iloc[ind].copy()  

    df['date'] = pd.to_datetime(df['date'], errors='coerce')
    df['creditdate'] = pd.to_datetime(df['creditdate'], errors='coerce')
    df['amount'] = pd.to_numeric(df['amount'], errors='coerce')
    df['amount'] = df.apply(lambda row: -row['amount'] if row['type'] == '支出' else row['amount'], axis=1)

    df = df.dropna(subset=['date'])

    df = df.sort_values(by=sortby)                
    return df

def credit_sum(Adder):    
    output = Adder.iloc[0].copy()    
    
    output['date'] = Adder['date'].max()
    output['type'] = "支出"
    output['category'] =  "クレジット"
    output['amount'] = Adder['amount'].sum()
    output['note'] = ' CREDIT SUMMARY'
    output['creditdate'] =  "----"
    
    return output

def last_adj(df,StartDay,EndDay):   
    df['date'] = pd.to_datetime(df['date'])
    df = df.sort_values(by='date')
    df["cumsum"] = df['amount'].cumsum()

    ## calc cash 
    ind = df['date'] <= pd.to_datetime(ToDay) 
    cash = df[ind].copy()
    CurrentSumation = cash.iloc[-1]['cumsum']
    cInd = cash[cash['method'].str.contains("CASH", case=False, na=False)].index.tolist()
    
    cash = cash.loc[cInd].copy()
    cash["cumsum"] = cash['amount'].cumsum()
    
    ind = (df['date'] >= StartDay) & (df['date'] <= EndDay)
    df = df[ind].copy()

    
    df['date'] = df['date'].dt.strftime(dayformat)
    df['note'] = df['note'].fillna('')
    
    df['creditdate'] = pd.to_datetime(df['creditdate'], errors='coerce')
    df['creditdate'] = df['creditdate'].dt.strftime(dayformat)
    df['creditdate'] = df['creditdate'].fillna('')
    return df, cash, CurrentSumation

def print_df2csv(df):
    # Print column names joined with commas
    print(','.join(df.columns))
    
    # Iterate over rows and print each as a CSV-formatted line
    for index, row in df.iterrows():
        print(','.join(map(str, row.values)))


## Duration check
def Cal_Duration(duration):
    A1 = duration.split("to")[0]
    A2 = duration.split("to")[1]
    if (A1 is None) or (A2 is None):
        print(f"Error !! input Duration.({sys.argv[0]})")
        sys.exit(1)

    D1 = try_datalize(A1)
    D2 = try_datalize(A2)
    if (D1 is None) and (D2 is None):
        D1 = ToDay
        D2 = ToDay + pd.DateOffset(months=1)
    elif D1 is None:
        if D2 < ToDay:
            D1 = D2
            D2 = ToDay+ pd.DateOffset(months=1)
        else:
            D1 = ToDay - pd.DateOffset(months=1)
    elif D2 is None:
        if D1 < ToDay:
            D2 = ToDay+ pd.DateOffset(months=1)
        else:
            D2 = D1
            D1 = ToDay -  pd.DateOffset(months=1)        

    A = pd.DataFrame({
        "date": [D1, D2]        
    })
    
    StartDay = A['date'].min()
    EndDay = A['date'].max()
    # print(f"{StartDay} to {EndDay}")
    return StartDay,EndDay

def try_datalize(A):
    try:
        return pd.to_datetime(A)
    except Exception as e:
        return None

if not(duration == "all"):
    StartDay, EndDay = Cal_Duration(duration)
else:
    StartDay, EndDay = Cal_Duration("1990-01-01 to 2100-01-01")

df = Read_csv('./CSV/transactions.csv')

# checkDF = df.sort_values('date')
# print_df2csv(checkDF)
# sys.exit()

if viewtype == "all":
    readList = ["Pre"]
    Start = "2025_03"

    CreditRealDayTF = (credit.lower() == 'enable') and (credit_realday.lower() == 'true')
    for f in list(["2025_03","2025_04","Pre"]):
        Name = f"./CSV/{str(f)}_Dcard_classfied.csv"
        Adder = Read_csv(Name, CreditRealDayTF, 'creditdate')
        # print(Adder)
        # print(f'\033[33m{" -" * 40}\033[0m')
        # sys.exit()

        if credit.lower() == 'disable':
            Series = credit_sum(Adder)
            df = pd.concat([df, Series.to_frame().T], ignore_index=True)
        else:
            df = pd.concat([df, Adder], ignore_index=True)



# JSONで出力
# print(json.dumps(df.to_dict(orient='records', force_ascii=False) indent=4))
df,cash, CS = last_adj(df,StartDay,EndDay)

if output_type == 'JSON':
    columns_order = ['date', 'type', 'method', 'category', 'amount', 'cumsum' ,'note', 'creditdate'] 
    df = df[columns_order]
    json_output = json.dumps(df.to_dict(orient='records'), indent=4, ensure_ascii=False)
    print(json_output)

    # print(json.dumps(df.to_dict(orient='records'), indent=4, ensure_ascii=False))
elif output_type == 'JSON-Category':
    X,jpX = subFun.print_byCategory(df) 
    X = X[['category', 'SumAmount']]
    a1 = pd.DataFrame({
        "category" : ["合計"], 
        "SumAmount" : [X['SumAmount'].sum()]
    })
           
    output = pd.concat([X, a1], ignore_index=True)
    json_output = json.dumps(output.to_dict(orient='records'), indent=4, ensure_ascii=False)
    print(json_output)
    

elif output_type == 'Current-Sumation':    
    A = locale.currency( CS, grouping=True )
    B = locale.currency( CS - cash.iloc[-1]['cumsum'], grouping=True )
    C = locale.currency( cash.iloc[-1]['cumsum'], grouping=True )
    response = {
        "sum": A,
        "bank": B,
        "cash": C
    }
    print(json.dumps(response, indent=4, ensure_ascii=False))
    # print(f'【Current Sumation】 : {A} <br>(Breakdown) BANK: {B} <br>(Breakdown) CASH: {C}')

else:    
    print_df2csv(cash)
    print(f' Start:{StartDay.strftime(dayformat)}, \n  End:{EndDay.strftime(dayformat)},\n CreditType:{credit},\n Sheet:{viewtype}')