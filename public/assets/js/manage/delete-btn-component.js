/******/ (() => { // webpackBootstrap
    var __webpack_exports__ = {};
    /*!*****************************************************!*\
      !*** ./resources/js/manage/delete-btn-component.js ***!
      \*****************************************************/
    var deletingElementsIdsBox = document.getElementById("deletingItemsBox");
    var checkAll = document.querySelector("#checkAll");
    if (checkAll) {
        checkAll.onclick = function () {
            var checkboxes = document.querySelectorAll('.form-check-all input[type="checkbox"]');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = this.checked;
                if (checkboxes[i].checked) {
                    checkboxes[i].closest("tr").classList.add("table-active");
                } else {
                    checkboxes[i].closest("tr").classList.remove("table-active");
                }
            }
        };
    }
    var clearDeleteBox = function clearDeleteBox() {
        deletingElementsIdsBox.innerHTML = "";
    };
    function pushItemToDeleteBox(id) {
        var input = document.createElement("input");
        input.type = "hidden";
        input.name = "ids[]";
        input.value = id;
        deletingElementsIdsBox.appendChild(input);
    }
    document.querySelector("#deleteMultiple").addEventListener("click", function () {
        var ids_array = [];
        var items = document.getElementsByName('chk_child');
        for (i = 0; i < items.length; i++) {
            if (items[i].checked === true) {
                ids_array.push(items[i].value);
            }
        }
        clearDeleteBox();
        if (ids_array.length) {
            for (var _i = 0; _i < ids_array.length; _i++) {
                pushItemToDeleteBox(ids_array[_i]);
            }
        }
    });
    Array.from(document.querySelectorAll(".destroy")).forEach(function (item) {
        item.addEventListener('click', function () {
            clearDeleteBox();
            pushItemToDeleteBox(this.getAttribute("data-item-id"));
        });
    });
    /******/ })()
;
