
function updateTable() {
    // input check
    let startDate = document.querySelector("input[name='startdate']").value;
    let endDate = document.querySelector("input[name='enddate']").value;
    // let creditTF = document.querySelector(".switch input[type='checkbox']").checked;
    let creditTF = document.getElementById('credit_visible').checked;
    let creditVisible = creditTF ? "Enable" : "Disable";
    let creditRealDayTF = document.getElementById('credit_realday').checked;
    let creditRealDay = creditRealDayTF ? "True" : "False";
    let selectedSheet = document.querySelector("select[name='viewtype']").value;

    let params = {
        startDate: startDate,
        endDate: endDate,
        credit_visible: creditVisible,
        credit_realday: creditRealDay,
        selected_sheet: selectedSheet
    };

 
    fetch('updatetable.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(params) // 修正：正しく JSON にする
    })
    .then(response => response.text())
    .then(data => {
         document.getElementById('main-panel').innerHTML = data;
    })
    .catch(error => console.error("Error:", error));
}

function toggleCreditVisible_old() {
    var CreditVisible = document.getElementById('credit_visible'); 
    var CreditRealday = document.getElementById('credit_realday'); 
    // console.log( ! CreditVisible.checked)    
    if ( ! CreditVisible.checked) {
        CreditRealday.disabled = true; // 無効化
        CreditRealday.checked = false; // チェックを外す
    } else {
        CreditRealday.disabled = false; // 有効化
    }
}

// add.php
function toggleCreditUse_old() {  //　2025/4/5UI変更で使用しなくなった。
    var type = document.getElementById('type').value; // 選ばれた種類を取得
    var creditCheckbox = document.getElementById('credit_use'); // チェックボックスの要素
    
    // 「支出」または「収入」以外の場合は無効にし、チェックを外す
    if (type !== '支出' && type !== '収入') {
        creditCheckbox.disabled = true; // 無効化
        creditCheckbox.checked = false; // チェックを外す
    } else {
        creditCheckbox.disabled = false; // 有効化
    }
}

async function type_Changed () {
    var type = document.getElementById('type').value;
    var Method = document.getElementById('method'); 
    var Category = document.getElementById('category');
    // Reset children
    Method.innerHTML = "";
    Category.innerHTML = "";

    if (type === '支出') {
        addDefaultOption(Method);
        addOption(Method, "Credit", "Credit");
        addOption(Method, "CASH", "CASH");
        addOption(Method, "BANK", "BANK");
        addDefaultOption(Category);
        const category_list = await loadCategory('A',Category);
        console.log(category_list) ;     
                                        
    } else if (type === "収入") {
        addDefaultOption(Method)
        addOption(Method, "CASH", "CASH");
        addOption(Method, "BANK", "BANK");
        addDefaultOption(Category);
        const category_list = await loadCategory('B', Category);
        
    } else if (type === "Bank to Cash" || type === "Cash to Bank") {
        addOption(Method, "Exchange", "Exchange");
        addOption(Category, "Exchange", "Exchange");
        console.log('Selected: ' + type)
    }
}


    // 選択肢追加関数
function addDefaultOption(selectElem) {
    const defaultOption = document.createElement("option");
    defaultOption.value = "";
    defaultOption.textContent = "PLEASE SELECT";
    defaultOption.selected = true;
    defaultOption.disabled = true;
    defaultOption.hidden = true;    
    selectElem.insertBefore(defaultOption, selectElem.firstChild);
}
function addOption(selectElem, value, text) {
    const opt = document.createElement("option");
    opt.value = value;
    opt.textContent = text;
    selectElem.appendChild(opt);
}

// file-read: Category .txt
async function loadCategory(input, Category) {
    const response = await fetch('./CSS/Category.txt');
    const text = await response.text();
    const lines = text.trim().split('\n');

    const A = [];
    const B = [];
    const C = [];
    let c = 0;
    const sepTF = '#';

    for (const line of lines) {
      const [category] = line.split(',').map(s => s.trim());
      if ( category === sepTF ){
        c = c + 1;
        continue
      }

      if (c === 1) {
        if (category) A.push(category);
      } else if (c ==2) {
        if (category) B.push(category);
      } else {
        if (category) C.push(category);
      }
    }

        
    let output;
    if (input === 'A') {
      output = A;
    } else if (input === 'B') {
      output = B;
    } else {
      output = null;
    }

    for (const item of output) {
        const option = document.createElement("option");
        option.value = item;
        option.textContent = item;
        Category.appendChild(option);
      }
    return output;
  }