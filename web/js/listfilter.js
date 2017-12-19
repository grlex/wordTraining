

(function( $ ){

    var methods = {
        init : function( options ) {

            return this.each(function() {

                var $this = $(this),
                    data = $this.data('listfilter');

                if (!data) {

                    $this.addClass('filterlist');
                    var list = $this.find(options['list'] || '.list').addClass('fl-list');

                    data = {
                        target: $this,
                        list: list,
                        listItemSelector: '.fl-list > li',
                        filterInput: $this.find(options['filterInput'] || '.filter-input'),
                        dataElemPath: options['dataElem'] || '',
                        dataElemTarget: options['dataElemTarget'] || ['text'] // or ['attr', 'name'] , ['prop', 'name']
                    };

                    $this.data('listfilter', data);

                    var timer= false;
                    var filterFunc = $.fn.listfilter.filter;
                    data.filterInput.keyup(function(){
                        clearTimeout(timer);
                        timer = setTimeout(filterFunc.bind(null,data,this.value), 500);
                    });
                }
            });
        },
        filter: function(filterText){
            return this.each(function() {

                var $this = $(this),
                    data = $this.data('listfilter');

                if (data) {
                    $.fn.listfilter.filter(data, filterText);
                }
            });
        },

        destroy : function( ) {

            return this.each(function(){

                var $this = $(this),
                    data = $this.data('tooltip');


                $this.removeData('listfilter');

            })

        }
    };

    $.fn.listfilter = function( method ) {

        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Метод с именем ' +  method + ' не существует для jQuery.listfilter' );
        }
    };


    $.fn.listfilter.filter = function(filterListData, filterText){

        if(!filterText){
            filterListData.list.children(filterListData.listItemSelector).addClass('fl-filtered');
            return;
        }
        var dataElements = filterListData.list.children(filterListData.listItemSelector).find(filterListData.dataElemPath);

        dataElements.each(function(index, elem){

            var filtered = $.fn.listfilter.filterDataElem(elem, filterListData.dataElemTarget, filterText);
            var listItem = $(elem).closest(filterListData.listItemSelector);
            if(filtered) listItem.addClass('fl-filtered');
            else listItem.removeClass('fl-filtered');

        });

    };
    $.fn.listfilter.filterDataElem = function(elem, elemTarget, filterText){
        var dataText = "";
        switch(elemTarget[0]){
            case 'text':
                dataText = elem.textContent || elem.innerText || "";
                break;
            case 'attr':
                dataText = elem.getAttribute(elemTarget[1]) || "";
                break;
            case 'prop':
                dataText = elem[elemTarget[1]] || "";
                break;
            default:
                break;
        }
        return dataText.startsWith(filterText);
    };



})( jQuery );
