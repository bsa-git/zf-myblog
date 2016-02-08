<form id="filter-container_1" method="POST" action="/" style="display: none">
    <fieldset class="myfilter">
        <legend>{'Фильтр'|translate}</legend>
        <div class="filter-box" id="filter_11" >
            <img src="{'/images/system/empty20x20.png'|urlres}" alt="" />
            <input class="filter-input" type="text" id="fields-input-filter_11" />
            <input class="filter-compare" type="text" id="compare-input-filter_11" />
            <input class="filter-input" type="text" id="values-input-filter_11" />
            <img src="{'/images/system/search-indicator.gif'|urlres}" id="indicator-filter_11" style="display: none" alt="" />
            <input type="image" id="add-filter_11" src="{'/images/system/more_20x20.gif'|urlres}" alt title="{'Добавить фильтр'|translate}">
            <input type="image" id="delete-filter_11" src="{'/images/system/delete_button_dis.gif'|urlres}" alt="{'Удалить фильтр'|translate}">
        </div>
        <br />
        <div style="margin-left: 23px;">
            <input type="submit" class="btn btn-primary" value="{'OK'|translate}" />
            <input type="reset" class="btn" value="{'Очистить'|translate}" />
        </div>
    </fieldset>
</form>