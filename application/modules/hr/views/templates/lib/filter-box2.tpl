<form id="filter-container_2" method="POST" action="/" style="display: none">
    <fieldset class="myfilter">
        <legend>{'Фильтр'|translate}</legend>
        <div class="box" id="filter_21" >
            <img src="{'/images/system/empty20x20.png'|urlres}" alt="" />
            <input class="filter-input" type="text" id="fields-input-filter_21" />
            <input class="filter-compare" type="text" id="compare-input-filter_21" />
            <input class="filter-input" type="text" id="values-input-filter_21" />
            <img src="{'/images/system/search-indicator.gif'|urlres}" id="indicator-filter_21" style="display: none" alt="" />
            <input type="image" id="add-filter_21" src="{'/images/system/more_20x20.gif'|urlres}" alt title="{'Добавить фильтр'|translate}">
            <input type="image" id="delete-filter_21" src="{'/images/system/delete_button_dis.gif'|urlres}" alt="{'Удалить фильтр'|translate}">
        </div>
        <div class="box">
            <input type="submit" value="{'OK'|translate}" />
            <input type="reset" value="{'Очистить'|translate}" />
        </div>
    </fieldset>
</form>