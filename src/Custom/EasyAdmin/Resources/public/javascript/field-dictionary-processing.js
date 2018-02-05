/**
 * Created by Aspire on 21.12.2017.
 */


class DictionaryProcessingWidget{
    constructor(widget){
        this.widget = widget;
        this.processedElem = widget.find('.processed');
        this.totalElem = widget.find('.total');
        this.progressBarElem = widget.find('.progress-bar');
        widget.find('.pause').click(this.pause.bind(this));
        widget.find('.resume').click(this.resume.bind(this));
        var dictionaryId = widget.data('dictionary-id');
        this.processActionUri = '/admin/dictionary/process/'+dictionaryId;
        this.init();
    }

    doneHandler(){
        this.widget.removeClass('processing paused').addClass('done');

    }

    pausedHandler(){
        this.widget.removeClass('processing').addClass('paused');
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
                processed: this.widget.data('processed'),
                total: this.widget.data('total'),
                status: this.widget.data('status')
            };

        this.processedElem.text(data.processed);
        this.totalElem.text(data.total);
        if(data.total>0)
            this.progressBarElem.css('width', 100 * data.processed / data.total + '%');
        else
            this.progressBarElem.css('width', '100%');
    }

    start(){
        $.post(this.processActionUri+'?action=start');
        setTimeout(this.update.bind(this),1000);
    }
    update(){
        $.getJSON(this.processActionUri+'?action=status', this.updateHandler.bind(this));
    }
    pause(){
        $.post(this.processActionUri+'?action=pause');
    }
    resume(){
        this.widget.removeClass('paused').addClass('processing');
        $.post(this.processActionUri+'?action=resume');
        setTimeout(this.update.bind(this),1000);
    }
    init(){
        /*
         const STATUS_PENDING = 1;
         const STATUS_PROCESSING = 2;
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
        this.widget.addClass('processing');


    }


}

$(document).ready(function () {
    $('.dictionary-processing-widget').each(function (index, widget) {
        widget = $(widget);
        new DictionaryProcessingWidget(widget);
    });
});
