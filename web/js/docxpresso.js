/*Dynamical table utilities*/
            
//Grabbing table data
function table2Array (element) {
    var counter = 0;
    var data = new Array();
    $('#' + element).find('tr').each(function(){
        var tds = 0;
        data[counter] = new Array();
            $(this).find('.keyvalue').each(function(){
                data[counter][tds] = $(this).text();
                tds++;
            });
            counter++;
    });
    return data;
}
function array2Assoc (data) {
    n = data.length;
    d = new Array();
    for (k = 0; k < n; k++ ) {
        key = data[k][0].toString();
        d[key] = data[k][1];
    }
    return d;
}

function assoc2Array (data) {
    d = new Array();
    counter = 0;
    for (key in data ) {
        d[counter] = new Array(key, data[key]);
        counter++;
    }
    return d;
}

function table2JSON(element){
    json = JSON.stringify(table2Array(element));
    return json;
}

//auxiliary vars
var rowStrut = new Array();
rowStrut[0] = '<tr class="toclone"><td class="sortHandler"><i class="fa fa-arrows"></i></td>';               
rowStrut[1] = '<td class="butt"><button type="button" class="btn btn-primary btn-xs clone"><i class="fa fa-plus"></i></button><button type="button" class="btn btn-danger btn-xs delete"><i class="fa fa-times"></i></button></td>';
rowStrut[2] = '<td class="keyvalue" contenteditable="true">';
rowStrut[3] = '</td><td class="keyvalue" contenteditable="true">';
rowStrut[4] = '</td></tr>';

function array2Table (element, data) {
    htm = '';
    l = data.length;
    for (k = 0; k < l; k++) {
        htm += rowStrut[0] + rowStrut[1] + rowStrut[2] + data[k][0] + rowStrut[3];
        if (data[k][1]){
        htm += data[k][1];  
        }
        htm += rowStrut[4];
    }
    $('#' + element).html(htm);
}
//method using cloning
function array2TableByCloning(element, data){
    //first get the number of existing rows
    var rows = $('#' + element + ' tr');
    number = rows.length;
    //start cliking on the last clone button
    len = data.length;
    var q = 0
    timer = setInterval(function() { 
        $('#' + element + ' tr').eq(number - 1 + q).find('button.clone').click();
        $('#' + element + ' tr:last')
                .find('.keyvalue')
                .each(function(index) {
                    $(this).text(data[q][index]);
                });
                q++;
                if (q == len) {
                    for(k = 0; k < number; k++) {
                        rows.eq(k).find('button.delete').click();
                    }
                    clearInterval(timer);
                }
        }, 0)    
}

//sorting data by key value

function assocSort (data) {
    ref = new Array();
    counter = 0;
    for (key in data) {
        ref[counter] = key;
        counter++;
    }
    refSorted = ref.sort();
    d = new Array();
    l = refSorted.length;
    for (k = 0; k < l; k++) {
        d[refSorted[k]] = data[refSorted[k]];
    }
    return d;
}

function sortTableData (element) {
    data = table2Array(element);
    assoc = array2Assoc(data);
    newAssoc = assocSort(assoc);
    newData = assoc2Array(newAssoc);
    array2Table(element, newData);
}


