
window.onload = function() {
    index();
};

function request(action, reqData, func) {
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
            func(response);
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
            stroke: "#fff", // цвета ребра?
            fill: "#5a5", // цвета ребра?
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
    var func = function(adjList) {
        buildGraph(adjList);
    };
    var data = new FormData($('#createNewGraph_form')[0]);
    request('create', data, func);
}

function addVertexProcess() {
    event.preventDefault();
    if (!$( "#addVertex_name" )[0].value.trim()) {
        alert('Введите имя вершины');
        return;
    }
    var func = function(adjList) {
        buildGraph(adjList);
    };
    var data = new FormData($('#addVertex_form')[0]);
    request('addVertex', data, func);
}

function delVertexProcess() {
    event.preventDefault();
    if (!$( "#delVertex_name" )[0].value.trim()) {
        alert('Введите имя вершины');
        return;
    }
    var func = function(adjList) {
        buildGraph(adjList);
    };
    var data = new FormData($('#delVertex_form')[0]);
    request('delVertex', data, func);
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
    var func = function(adjList) {
        buildGraph(adjList);
    };
    var data = new FormData($('#addEdge_form')[0]);
    request('addEdge', data, func);
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
    var func = function(adjList) {
        buildGraph(adjList);
    };
    var data = new FormData($('#delEdge_form')[0]);
    request('delEdge', data, func);
}

function index() {
    var func = function(adjList) {
        buildGraph(adjList);
    };
    request('index', '', func);
}

function toggleFile() {
    $('.file_input').css('display', 'block');
}

function collapseFile() {
    $('.file_input').css('display', 'none');
}

// function buildGraph(data) {
// //     var g = new Graph();
// //     // добавляем узел с id "bebebe" и подписью "stand alone"
// //     // последний аргумент метода addNode - необязательный
// //     g.addNode("bebebe", {label: "stand alone"});
// //     for (var i = 1; i <= 13; i++) {
// //         // метод addEdge(a, b) создает ребро между узлами а и b
// //         // если узлы a и b еще не созданы, они создадутся автоматически
// //         g.addEdge(i, (i + 3) % 5 + 1);
// //         var j = (i + 7) % 5 + 1;
// //
// //         // можно указать дополнительные свойства ребра
// //         g.addEdge(i, j, {
// //             directed: true, // ориентированное ребро
// //             stroke: "#fff", fill: "#5a5", // цвета ребра
// //             label: i + ":" + j // надпись над ребром
// //         });
// //     }
// //     // вычисляем расположение вершин перед выводом
// //     var layouter = new Graph.Layout.Spring(g);
// //     layouter.layout();
// //     // выводим граф
// //     var w = $('#playground').css('width');
// //     w = w.replace('px', '');
// //     var renderer = new Graph.Renderer.Raphael('canvas', g, Number(w));
// //     renderer.draw();
// // }

