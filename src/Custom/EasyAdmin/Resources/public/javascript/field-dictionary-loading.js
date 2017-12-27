/**
 * Created by Aspire on 21.12.2017.
 */


class DictionaryLoadingWidget{
    constructor(widget){
        this.widget = widget;
        this.loadedElem = widget.find('.loaded');
        this.totalElem = widget.find('.total');
        this.progressBarElem = widget.find('.progress-bar');
        widget.find('.pause').click(this.pause.bind(this));
        widget.find('.resume').click(this.resume.bind(this));
        this.loadingIconElem = widget.find('.icon.loading');
        var dictionaryId = widget.data('dictionary-id');
        this.loadActionUri = '/admin/dictionary/load/'+dictionaryId;
        this.loadingAnimation = {
            start: function (iconElem) {
                iconElem.data('animating', true);
                iconElem.data('startTime', performance.now());
                this.redraw(iconElem, performance.now());
            },
            redraw: function redraw(iconElem, time) {
                iconElem.css({'transform': 'rotate(' + ((time - iconElem.data('startTime')) >> 1) + 'deg)'});
                if (iconElem.data('animating'))
                    requestAnimationFrame(redraw.bind(null, iconElem));
            },
            stop: function (iconElem) {
                iconElem.data('animating', false);
            }
        };
        this.init();
    }

    doneHandler(){
        this.widget.removeClass('loading paused').addClass('done');
        this.loadingAnimation.stop(this.loadingIconElem);
    }

    pausedHandler(){
        this.widget.removeClass('loading').addClass('paused');
        this.loadingAnimation.stop(this.loadingIconElem);
    }

    updateHandler(data){

        this.updateCounterElems(data);
        switch(data.status){
            case 3:
                this.doneHandler();
                break;
            case 5:
                this.pausedHandler();
                break;
            default:
                setTimeout(this.update.bind(this), 1000);
                break;
        }
    }



    updateCounterElems(data){
        data = data || {
                loaded: this.widget.data('loaded'),
                total: this.widget.data('total'),
                status: this.widget.data('status')
            };

        this.loadedElem.text(data.loaded);
        this.totalElem.text(data.total);
        this.progressBarElem.css('width', 100 * data.loaded / data.total + '%');
    }

    start(){
        $.post(this.loadActionUri+'?action=start');
        setTimeout(this.update.bind(this),1000);
    }
    update(){
        $.getJSON(this.loadActionUri+'?action=status', this.updateHandler.bind(this));
    }
    pause(){
        $.post(this.loadActionUri+'?action=pause');
    }
    resume(){
        this.loadingAnimation.start(this.loadingIconElem);
        this.widget.removeClass('paused').addClass('loading');
        $.post(this.loadActionUri+'?action=resume');
        setTimeout(this.update.bind(this),1000);
    }
    init(){
        /*
         const STATUS_PENDING = 1;
         const STATUS_LOADING = 2;
         const STATUS_DONE = 3;
         const STATUS_PAUSING = 4;
         const STATUS_PAUSED = 5;
        */
        this.updateCounterElems();
        var status = this.widget.data('status');
        switch(status){
            case 1:
                this.start();
                break;
            case 3:
                this.doneHandler();
                return;
            case 5:
                this.pausedHandler();
                return;
            default:
                this.update();
                break;
        }
        this.loadingAnimation.start(this.loadingIconElem);
        this.widget.addClass('loading');


    }


}

$(document).ready(function () {
    $('.dictionary-loading-widget').each(function (index, widget) {
        widget = $(widget);
        new DictionaryLoadingWidget(widget);
    });
});
