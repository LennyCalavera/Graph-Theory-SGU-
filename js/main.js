
window.onload = function() {
    index();
};

function request(action, reqData, func = null) {
    $.ajax({
        url: '/backend/RequestHandler.php',
        type: "POST",
        data: reqData,
        dataType: 'json',
        headers: {
            graphAction: action,
        },
        processData: false,
        contentType: false,
        success: function (response) {
            if ((typeof response.errors !== 'undefined') && (response.errors.length != 0)) {
                var errors = response.errors.join('\n');
                alert('Ошибка!\n' + errors);
                return
            }
            if (func) {
                func(response);
            }
            else {
                buildGraph(response);
            }
        },
        error: function(response) {
            alert('Ошибка!\nДанные не отправлены/невалидный ответ/нет ответа');
        }
    });
}

function buildGraph(data) {
    document.getElementById('canvas').innerHTML = '';
    var directed = data.directed ? 'Ориентированный граф' : 'Неориентированный граф';
    document.getElementById('orientation_mode').value = directed;

    var g = new Graph();
    $.each(data.vertexes, function( index, vertexName ) {
        g.addNode(vertexName, {label: vertexName});
    });
    $.each(data.edges, function( index, edge ) {
        g.addEdge(edge.from, edge.to, {
            directed: data.isDirected, // ориентированное ребро
            stroke: "#fff", // цвета ребра внутри
            fill: "#5a5", // цвета ребра снаружи
            label: edge.weight // надпись над ребром
        });
    });
    // вычисляем расположение вершин перед выводом
    var layouter = new Graph.Layout.Spring(g);
    layouter.layout();
    // выводим граф
    var w = $('#playground').css('width');
    w = w.replace('px', '');
    var renderer = new Graph.Renderer.Raphael('canvas', g, Number(w));
    renderer.draw();
}

function createNewGraph() {
    event.preventDefault();
    if (!$( "#createNewGraph_fromFile" )[0].checked && !$( "#createNewGraph_empty" )[0].checked) {
        alert('Выберите \"Пустой\" или \"Из файла .json\"');
        return;
    }
    if ($( "#createNewGraph_fromFile" )[0].checked) {
        if ($("#file_input")[0].files.length == 0) {
            alert('Файл не выбран');
            return;
        }
        var name = $("#file_input")[0].files[0].name;
        if (name.substr(name.length - 5) != '.json') {
            alert('Разрешены только json файлы');
            return;
        }
    }
    var data = new FormData($('#createNewGraph_form')[0]);
    request('create', data);
}

function addVertexProcess() {
    event.preventDefault();
    if (!$( "#addVertex_name" )[0].value.trim()) {
        alert('Введите имя вершины');
        return;
    }
    var data = new FormData($('#addVertex_form')[0]);
    request('addVertex', data);
}

function delVertexProcess() {
    event.preventDefault();
    if (!$( "#delVertex_name" )[0].value.trim()) {
        alert('Введите имя вершины');
        return;
    }
    var data = new FormData($('#delVertex_form')[0]);
    request('delVertex', data);
}

function addEdgeProcess() {
    event.preventDefault();
    if (!$( "#addEdge_name1" )[0].value.trim() || !$( "#addEdge_name2" )[0].value.trim()) {
        alert('Введите имена вершин');
        return;
    }
    if ($( "#addEdge_name1" )[0].value === $( "#addEdge_name2" )[0].value) {
        alert('Одинаковые имена вершин');
        return;
    }
    var data = new FormData($('#addEdge_form')[0]);
    request('addEdge', data);
}

function delEdgeProcess() {
    event.preventDefault();
    if (!$( "#delEdge_name1" )[0].value.trim() || !$( "#delEdge_name2" )[0].value.trim()) {
        alert('Введите имена вершин');
        return;
    }
    if ($( "#delEdge_name1" )[0].value === $( "#delEdge_name2" )[0].value) {
        alert('Одинаковые имена вершин');
        return;
    }
    var data = new FormData($('#delEdge_form')[0]);
    request('delEdge', data);
}

function index() {
    request('index', '');
}

function toggleFile() {
    $('.file_input_hidden').css('display', 'block');
}

function collapseFile() {
    $('.file_input_hidden').css('display', 'none');
}

function task1() {
    event.preventDefault();
    request('task1', '');
}

function task2() {
    event.preventDefault();
    if ($("#task2_file1")[0].files.length == 0 || $("#task2_file2")[0].files.length == 0) {
        alert('Файлы не выбраны');
        return;
    }
    var fileName1 = $("#task2_file1")[0].files[0].name;
    var fileName2 = $("#task2_file2")[0].files[0].name;
    if (fileName1.substr(fileName1.length - 5) != '.json' ||
        fileName2.substr(fileName2.length - 5) != '.json'
    ) {
        alert('Разрешены только json файлы');
        return;
    }
    var data = new FormData($('#task2_form')[0]);
    request('task2', data);
}

function task3() {
    event.preventDefault();
    taskWithoutBuild('task3');
}

function task4() {
    event.preventDefault();
    request('task4', '');
}

function task5() {
    event.preventDefault();
    taskWithoutBuild('task5');
}

function task6() {
    event.preventDefault();
    taskWithoutBuild('task6');
}

function task7() {
    event.preventDefault();
    taskWithoutBuild('task7');
}

function task8() {
    event.preventDefault();
    taskWithoutBuild('task8');
}

function taskWithoutBuild(action) {
    var func = function (response) {
        if (response.answer) {
            alert(response.answer);
        }
        else {
            alert('Ответ не получен');
        }
    };
    request(action, '', func);
}