class subFun_balance:
    import pandas as pd
    import os,sys
    import numpy as np
    import locale
    import colorsys

    try:
        locale.setlocale(locale.LC_ALL, 'ja_JP.UTF-8')  # Unix
    except locale.Error:
        try:
            locale.setlocale(locale.LC_ALL, 'Japanese_Japan.932')  # Windows
        except locale.Error:
            print("ロケールが設定できません。")    

    category_color_txt = './CSS/Category.txt'
    LIST = pd.read_csv('./CSS/creditNote2category.list')
    # if len(sys.argv) < 2:
        # print(f"\033[5;31m ERROR : {sys.argv[0]} \033[0m\n\t Input need 2(filename, PayDate)")
        # sys.exit()
    fname = sys.argv[1] if len(sys.argv) > 1 else None
    PayDate = sys.argv[2] if len(sys.argv) > 2 else None

    @classmethod    
    def read_category_color(slf):
        with open(slf.category_color_txt, "r", encoding="utf-8") as f:
            L = [l.strip() for l in f]

        Color = {}
        ColorBG = {}
        for line in L:
            Sep = line.split(', ') 
            key = Sep[0]
            if key.startswith("#"):
                continue

            if len(key) == 0:
                continue

            value1 = tuple(eval(Sep[1]))  # `eval()` でタプル化
            if len(Sep) > 2:
                value2 = tuple(eval(Sep[2]))
            else:
                value2 = tuple(eval('(255,255,255)'))
            Color[key] = value1
            ColorBG[key] = value2

        return Color,ColorBG

    def Note2Uniq(Note):
        NOTE = set()
        for p in Note:
            NOTE.add(p)
        return list(Note)        

    @classmethod
    def note2cat(slf,STR):
        output = "Other"
        TF = True
        for i,p in enumerate(slf.LIST['Note']):
            if p in STR:
                output = str(slf.LIST.at[i,'Category'])        
                MOD=" "*(20 - len(STR))  
                print(f'\t{STR} :{MOD}\t{output}')
                TF=False
                break
                # return str(output)
        if TF:
            MOD=" "*(20 - len(STR))  
            print(f'\033[42m\t{STR} \033[0m:{MOD}\t{output}')    
        return output

    @classmethod
    def get_data(slf):
        if slf.fname is None:
            print(f"\033[5;31m ERROR : {slf.sys.argv[0]} \033[0m\n\t Input need filename (and PayDate as OPTION)")
            slf.exit(1)
        df = slf.pd.read_csv(slf.fname)
        try:
            Date=slf.pd.to_datetime(df['ご利用年月日'].copy())
            Note = df['利用店名'].copy()
            Amount = df['ご利用金額'].copy()
        except Exception as e:
            # ご利用年月日","利用店名","利用金額
            Date = slf.pd.to_datetime(df['ご利用年月日'].copy())
            Note = df['利用店名'].copy()
            Amount = df['利用金額'].copy()

        X = slf.pd.DataFrame({
            'date':Date,
            'amount':Amount,
            'note':Note
        })
        return X

    @classmethod
    def note2caterogy(slf,X):
        print(f"\033[31m $Categories !! {' = #'*20}\033[0m")
        for i,p in enumerate(X['note']):
            # print(str(p))
            X.at[i,'category'] = slf.note2cat(str(p))    
        X['amount'] = X["amount"].str.replace(",", "").astype(float)
        locale_currency =  slf.locale.currency(X['amount'].sum() , grouping=True )

        print(f"\t Sumation : \033[33m{locale_currency}\033[0m")
        print(f"\033[31m{' = #'*25}\033[0m")
        X = X.sort_values(by='date')
        return X

    @classmethod
    def main(slf):  
        X = slf.get_data()
        X = slf.note2caterogy(X)

        if slf.PayDate is None:
            # print(X)
            jpX, String = slf.print_byCategory(X)
            print(String)
            # print(jpX[['category', 'jp_YEN']])
        else:
            writeName = slf.fname.replace('.csv','_classfied.csv')
            slf.write_CSV(writeName, X)

    @classmethod
    def print_byCategory(slf,X):
        try:
            ind = X['type'] == '支出'
            X = X[ind].copy()
        except Exception as e:
            print(f'\t\033[33m{e} \033[0m')
        
        Label = set()
        for p in X['category']:
            Label.add(str(p))
        L = list(Label)

        SumAmount = slf.np.zeros(len(L))
        jp = []
        # print(X.columns)
        
        # print(X.shape)
        # 
        # print(len(X['category']))
        # return None 


        for j, target in enumerate(L):
            S = 0
            for i, C in enumerate(X['category']):                
                if C == target:
                    a = X.iloc[i]['amount']
                    S += a
            jpS = slf.locale.currency(S , grouping=True )
            jp.append(jpS)

            SumAmount[j] = S

        S = slf.pd.DataFrame({'category': L, 'SumAmount': SumAmount, 'jp_YEN': jp})
        S = S.sort_values(by='SumAmount',ascending=False).reset_index(drop=True)
        # print(S[['category', 'jp_YEN']])
        
        BG, Color = slf.read_category_color()
        # print(Color)
        # print(BG)
        STR = ''
        for i in range(len(S)):
            key = S.iloc[i]['category']
            yen = S.iloc[i]['jp_YEN']
            add_space = '　' * (5 - len(key))
            add_spaceyen = ' ' * (9 - len(yen))
            r,g,b = Color[key]            
            c = slf.rgb2ANSIcolor(r,g,b)
            r,g,b = BG[key]
            bg = slf.rgb2ANSIcolor(r,g,b)
            add_string = f'\t\033[3{c};4{bg}m {key}{add_space}:{add_spaceyen}{yen}  \033[0m\n'
            STR += add_string 
            
        # print(STR)
        return S, STR

    @classmethod
    def write_CSV(slf,writeName,X):
        with open(writeName,'w') as f:
            for i,d in enumerate(X['date']):
                a=str(X.at[i,'amount']).replace(',','')
                c=X.at[i,'category']
                n=X.at[i,'note']
                try:
                    D=d.strftime('%Y-%m-%d')
                except Exception as e:
                    D=d
                f.write(f"{slf.PayDate},支出,{c},{a},{n},{D}\n")

    
    @classmethod
    def rgb2ANSIcolor(slf,r, g, b):
        h, s, v = slf.colorsys.rgb_to_hsv(r / 255.0, g / 255.0, b / 255.0)
        if s < 0.5:
            if v < 0.5:
                return 0  # 黒
            else:
                return 7  # 白
        else:            
            H = round((h * 6) - 0.5) % 6              
            return 1+H

    
    
    
    
    
    


if __name__ == '__main__':
    subFun_balance.main()
