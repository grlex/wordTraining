/**
 * Created by Aspire on 02.02.2018.
 */

class BackgroundImagesHandler {

    constructor(collection){
        this.collection = collection;
        this.container = collection.find('.background-images');
        this.container.magnificPopup({
            delegate: '.background-image a.image',
            type: 'image',
            gallery: {
                enabled: true
            }
        });
        this.container.on('click', '.background-image .control.removing', this.onControlClick.bind(this, 'removed'));
        this.container.on('click', '.background-image .control.repeating', this.onControlClick.bind(this, 'repeated'));
        this.container.on('click', '.background-image .control.activity', this.onControlClick.bind(this, 'inactive'));
        this.imageWidgetPrototype = this.container.attr('data-prototype');
        this.collection.on('click', '.actions .add-action', this.onAddActionClick.bind(this));
        this.collection.on('click', '.actions .clear-action', this.onGroupActionClick.bind(this,'.removing', 'removed'));
        this.collection.on('click', '.actions .deactivate-action', this.onGroupActionClick.bind(this,'.activity', 'inactive'));
        this.container.find('.background-image.repeated').find('.control.repeating input.checker').prop('checked', true);
        this.container.find('.background-image.inactive').find('.control.activity input.checker').prop('checked', true);
        this.processingStrap = this.collection.find('.processing-strap');
    }
    onControlClick(statusClass, e){
        e.stopPropagation();
        var controlElem = $(e.target).closest('.control');
        var checkbox = controlElem.find('input.checker').get(0);
        checkbox.checked = !checkbox.checked;

        controlElem.closest('.background-image')
            .removeClass(statusClass)
            .addClass(checkbox.checked ? statusClass: '');
    }

    onGroupActionClick(controlSelector, statusClass, event){
        var reverse = $(event.target).hasClass('reverse');
        this.container.children('.background-image').each(function(i, elem){
            elem = $(elem);
            if(reverse){
                elem.removeClass(statusClass);
                elem.find(controlSelector + ' .checker').prop('checked', false)
            }
            else{
                elem.addClass(statusClass);
                elem.find(controlSelector + ' .checker').prop('checked', true)
            }
        });
        $(event.target).toggleClass('reverse');
    }

    onAddActionClick(event){
        if (event.preventDefault) event.preventDefault(); else event.returnValue = false;

        // Use a counter to avoid having the same index more than once
        var numItems = this.collection.data('count') || this.container.children('.background-image').length;

        this.collection.find('.collection-empty').remove();

        var newItem = this.collection.attr('data-prototype')
                .replace(/__name__/g,   numItems );

        // Increment the counter and store it in the collection
        this.collection.data('count', ++numItems);


        $(newItem).appendTo(this.container).hide().click().bind('change',  this.onNewBackgroundsFileElemChange.bind(this));
    }


    onNewBackgroundsFileElemChange(e){
        var self = this;
        var fileElem = e.target;
        if(fileElem.files.length > 0) {
            self.processingStrap.addClass('processing');
            self.collection.closest('form').addClass('disabled');
            var strapElem = self.processingStrap.children('.strap');
            var strapStep = Math.ceil(100/fileElem.files.length);

            var removeFieldName = fileElem.dataset.removeField;
            var repeatFieldName = fileElem.dataset.repeatField;
            for(var i = 0, count=0; i < fileElem.files.length; i++){
                var file = fileElem.files[i];
                var reader = new FileReader();
                reader.onload = function(i, e){
                    var backgroundItem = self.imageWidgetPrototype
                        .replace('__remove_name__', removeFieldName)
                        .replace('__repeat_name__', repeatFieldName)
                        .replace(/__i__/g, i)
                        .replace(/__(orig|small)_uri__/g, e.target.result)
                        .replace('__class__', '');
                    backgroundItem = $(backgroundItem);
                    backgroundItem.appendTo(self.container);
                    count+=1;
                    strapElem.width((count*strapStep)+'%');
                    if(count==fileElem.files.length){
                        self.processingStrap.removeClass('processing');
                        self.collection.closest('form').removeClass('disabled');
                    }
                }.bind(reader,i);
                reader.readAsDataURL(file);
            }
        }
    }
}

new BackgroundImagesHandler($('.backgrounds-collection'));