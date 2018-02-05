/**
 * Created by Aspire on 24.10.2017.
 */


class AbstractWordExercise {
    constructor(exerciseWidget, words){
        this.words = words;
        this.exerciseWidget = exerciseWidget;
        this.questionElem = this.exerciseWidget.find('.exercise-question');
        this.questionHeaderElem = this.questionElem.find('.question-header');
        this.questionBodyElem = this.questionElem.find('.question-body');
        this.questionFooterElem = this.questionElem.find('.question-footer');
        this.questionFinishElem = this.questionElem.find('.question-finish');
        this.answerElem = this.exerciseWidget.find('.exercise-answer');
        this.resultElem = this.exerciseWidget.find('.exercise-result');

        this.correctCounter = 0;
        this.incorrectCounter = 0;
        this.currentWordIndex = 0;
        this.currentWord = null;
        this.exercise = exerciseWidget.data('exercise');




        if( [3,4,6].includes(this.exercise)){
            this.initAudioExercise();
        }

        var self = this;
        this.questionElem.find('.exercise-nav-buttons .nav-btn.prev').click(this.goToPreviousWord.bind(this));
        this.questionElem.find('.exercise-nav-buttons .nav-btn.next').click(this.goToNextWord.bind(this));
        $(document.body).on('keydown', function(e){
            switch(e.which){
                case 37: // left
                    self.goToPreviousWord();
                    break;
                case 39: // right
                    self.goToNextWord();
                    break;
            }
        });
        this.questionFinishElem.find('button.finish').bind('click keypress', function(e){
            if(e.type == 'keypress' && e.which!=13) return;
            self.displayResult();
        });
        this.questionHeaderElem.find('.progress-counter .total').text(this.words.length);
        this.resultElem.find('.total .count').text(this.words.length);
    }

    static create(exerciseWidget, words){
        var exercise = exerciseWidget.data('exercise');
        switch(exercise){
            case 1:
            case 2:
            case 3:
            case 4:
                return new WordChoiceExercise(exerciseWidget, words);
            case 5:
            case 6:
                return new WordWritingExercise(exerciseWidget, words);
        }
    }

    initAudioExercise(){
        this.questionBodyElem.find('audio')
            .bind('canplay', function(e){
                $(e.target).closest('.word-pronounce-widget').removeClass('disabled');
                e.target.play();
            })
            .bind('error', function(e){
                $(e.target).closest('.word-pronounce-widget').addClass('disabled');
            });
        this.questionBodyElem.find('.pronounce-transcription-hint').appendTo(this.questionFooterElem);
    }

    start(){
        this.correctCounter = 0;
        this.incorrectCounter = 0;
        this.words.forEach(function(word){
            word.exerciseMeta = {
                status: 'opened' , // opened, locked, committed
                answerCorrect: false,
                answer: false
            };
        });

        this.questionFinishElem.hide();
        this.questionBodyElem.hide();
        if(this.words.length==0) {
            this.questionFinishElem.show();
        }
        else {
            this.questionBodyElem.show();
        }

        this.questionElem.fadeOut('fast');
        this.answerElem.fadeOut('fast');
        var self = this;
        this.resultElem.fadeOut('fast',function(){
            self.questionElem.fadeIn('fast');
            if(self.words.length > 0) {
                self.answerElem.fadeIn('fast');
                self.goToWord(0);
            }else{
                self.currentWordIndex = -1;
                self.updateCounters();
            }
        });


    }

    orderWords(order){
        this.words.sort(function(left, right){
            switch(order){
                case 'asc':
                    return left.spelling > right.spelling ? 1 : left.spelling < right.spelling ? -1 : 0;
                    break;
                case 'desc':
                    return left.spelling > right.spelling ? -1 : left.spelling < right.spelling ? 1 : 0;
                    break;
                case 'random':
                    return Math.random()-0.5;
                    break;
            }
        });
        this.start();
    }

    displayResult(){
        var self = this;
        this.resultElem.find('.correct .count').text(this.correctCounter);
        this.resultElem.find('.incorrect .count').text(this.incorrectCounter);
        this.resultElem.find('.skipped .count').text(this.words.length - this.correctCounter - this.incorrectCounter);
        var percentage = this.words.length ? Math.floor(100*this.correctCounter/this.words.length) : 0;
        this.resultElem.find('.percentage .value').text(percentage);
        this.resultElem.find('.score .score-stars .star').removeClass('empty-colored half filled').addClass('empty');
        var correctnessScore = 5*this.correctCounter/this.words.length;
        if(correctnessScore>0) {
            var starIndex = Math.ceil(correctnessScore);
            var starElem = this.resultElem.find('.score .score-stars .star-' + starIndex);
            starElem.removeClass('empty');
            if (correctnessScore % 1 == 0) starElem.addClass('filled');
            else if (correctnessScore % 1 > 0.5) starElem.addClass('half');
            else starElem.addClass('empty-colored');
            starElem.prevAll('.star').removeClass('empty').addClass('filled');
        }

        this.questionElem.fadeOut('fast', function(){
            self.resultElem.fadeIn('fast');
        });
    }

    goToNextWord(){
        if(this.currentWordIndex == this.words.length){
            return;
        }
        if(this.currentWordIndex + 1 == this.words.length){
            this.currentWordIndex += 1;
            this.questionBodyElem.hide();
            this.questionFinishElem.show().find('button').focus();
            this.answerElem.hide();
            return;
        }
        this.goToWord(this.currentWordIndex + 1);
    }

    goToPreviousWord(){
        if(this.currentWordIndex == 0){
            return;
        }
        if(this.currentWordIndex == this.words.length){
            this.questionFinishElem.hide();
            this.questionBodyElem.show();
            this.answerElem.show();
        }

        this.goToWord(this.currentWordIndex - 1);
    }

    goToWord(index){
        this.currentWordIndex = index % this.words.length;
        this.currentWord = this.words[this.currentWordIndex];
        this.updateQuestion();
        this.updateAnswer();
        this.updateCounters();
    }

    getAnswerText(word){
        /*
         const EXERCISE_CHOICE_TRANSLATION_FROM_SPELLING = 1;
         const EXERCISE_CHOICE_SPELLING_FROM_TRANSLATION = 2;
         const EXERCISE_CHOICE_SPELLING_FROM_PRONOUNCE = 3;
         const EXERCISE_CHOICE_TRANSLATION_FROM_PRONOUNCE = 4;
         const EXERCISE_TYPING_SPELLING_FROM_TRANSLATION = 5;
         const EXERCISE_TYPING_SPELLING_FROM_PRONOUNCE = 6;
         */
        switch(this.exercise){
            case 1:
            case 4:
                return word.translation;
            case 2:
            case 3:
            case 5:
            case 6:
                return word.spelling;
        }
    }

    updateAnswer(){}

    updateQuestion(){
        switch(this.exercise){
            case 1:
                this.questionBodyElem.text(this.currentWord.spelling);
                break;
            case 2:
            case 5:
                this.questionBodyElem.text(this.currentWord.translation);
                break;
            case 3:
            case 4:
            case 6:
                this.questionBodyElem.find('audio').get(0).src = this.currentWord.audioURL;
                this.updateAudioHint();
                break;

        }
    }

    updateCounters(){
        this.questionHeaderElem.find('.progress-counter > .current').text(this.currentWordIndex+1);
        this.questionHeaderElem.find('.correct-counter > .counter').text(this.correctCounter);
    }

    updateAudioHint(){
        this.questionFooterElem.find('.pronounce-transcription-hint .text')
            .text(this.currentWord.transcription);
    }

    lockAnswerStatus(){
        if(this.currentWord.exerciseMeta.status != 'opened') return false;
        this.currentWord.exerciseMeta.status  = 'locked';
        return true;
    }

    commitAnswerStatus(){
        if(this.currentWord.exerciseMeta.status  != 'locked') return false;
        this.currentWord.exerciseMeta.status  = 'committed';
        return true;
    }

    isOpenAnswerStatus(){
        return this.currentWord.exerciseMeta.status == 'opened';
    }

    winQuestion(){
        this.currentWord.exerciseMeta.answerCorrect = true;
        this.correctCounter++;
    }

    failQuestion(answer){
        //this.currentWord.exerciseMeta.answerCorrect = false;
        this.incorrectCounter++;
    }

    skipQuestion(answer){
        //this.currentWord.exerciseMeta.answerCorrect = false;
    }

}

class WordWritingExercise extends AbstractWordExercise{


    constructor(exerciseWidget, words){
        super(exerciseWidget, words);
        this.writingElem = this.answerElem.children('.written-answer');
        this.typingStopTimer = false;

        this.writingElem.children('input').bind('keyup', this.onWriteAnswerKeyUp.bind(this));
        this.writingElem.find('button.dont-know')
            .bind('mousedown keydown', this.onPreWritingFail.bind(this))
            .bind('mouseup mouseleave keyup', this.onPostWritingFail.bind(this));

    }

    onPreWritingFail(e){
        if(!this.lockAnswerStatus()) return;
        if(e.type=='keydown' && e.which != 13 ) return;
        var answer =  this.writingElem.children('input').val();
        var correctAnswer = this.writingElem.data('correct-answer');

        this.failQuestion(answer);

        this.writingElem.addClass('has-warning');
        this.writingElem.children('input')
            .val(correctAnswer)
            .attr('disabled', 'yes');
    }

    onPostWritingFail(e){
        if(!this.commitAnswerStatus()) return;
        if(e.type=='keyup' && e.which != 13 ) return;
        setTimeout(this.goToNextWord.bind(this), 500);

    }

    checkWrittenAnswer() {
        if(!this.isOpenAnswerStatus()) return;
        var answer =  this.writingElem.children('input').val();
        var correctAnswer =  this.writingElem.data('correct-answer');
        this.currentWord.exerciseMeta.answer = answer;
        if(correctAnswer.startsWith('to ') && !answer.startsWith('to '.substring(0,answer.length))){
            answer = 'to ' + answer;
        }


        if(correctAnswer.startsWith(answer)){
            if(correctAnswer == answer){
                this.writingElem.addClass('has-success');
                this.writingElem.children('input').attr('disabled', true);
                this.winQuestion();
                this.lockAnswerStatus();
                this.commitAnswerStatus();
                setTimeout(this.goToNextWord.bind(this), 500);
            }
        }

    }

    onWriteAnswerKeyUp(e){
        clearTimeout(this.typingStopTimer);
        this.typingStopTimer = setTimeout(
            this.checkWrittenAnswer.bind(this),
            500
        );
    }

    updateAnswer(){
        var correctAnswer = this.getAnswerText(this.currentWord);


        this.writingElem.data('correct-answer', correctAnswer)
            .removeClass('has-success has-warning has-error');
        var inputElem = this.writingElem.find('input[type=text]');
        if(this.currentWord.exerciseMeta.status == 'committed'){
            inputElem.attr('disabled', true)
                .val(correctAnswer);
            this.writingElem.addClass(this.currentWord.exerciseMeta.answerCorrect ? 'has-success' : 'has-error');
            this.writingElem.find('button.dont-know').addClass('disabled');

        }else{
            inputElem.removeAttr('disabled')
                .val(this.currentWord.exerciseMeta.answer ? this.currentWord.exerciseMeta.answer : '')
                .focus();
            this.writingElem.find('button.dont-know').removeClass('disabled');
        }

    }
}

class WordChoiceExercise extends AbstractWordExercise{

    constructor(exerciseWidget, words){
        super(exerciseWidget, words);

        this.overlay = this.answerElem.find('.overlay');
        this.choiceList = this.answerElem.find('.choice-list');

        // event listeners
        var self = this;
        this.overlay.bind('mousemove', function(e){

            var dismissDistance = self.overlay.data('dismissDistance');
            dismissDistance = dismissDistance ? dismissDistance : 0;
            if(dismissDistance === 0){
                self.overlay.data('lastMousePosition', e.pageX + e.pageY);
                self.overlay.data('dismissDistance', 1);
            }
            else if(dismissDistance < 20){
                var lastPosition = self.overlay.data('lastMousePosition');
                self.overlay.data('dismissDistance', dismissDistance + Math.abs(lastPosition - e.pageX - e.pageY));
                self.overlay.data('lastMousePosition', e.pageX + e.pageY);
            }
            else{
                self.overlay.data('dismissDistance', 0);
                self.overlay.hide();
            }

        });

        $(document.body).on('keydown', function(e){
            if(![38, 40].includes(e.which)) return;
            var currentChoiceElem = self.choiceList.children('.choice.current');
            if(currentChoiceElem.length == 0) {
                currentChoiceElem = self.choiceList.find('.choice:first-child');
            }
            else {
                currentChoiceElem.removeClass('current');
                switch (e.which) {
                    case 38: // up
                        if (currentChoiceElem.is('.choice:first-child')) {
                            currentChoiceElem = currentChoiceElem.nextAll('.choice:last-child');
                        }
                        else {
                            currentChoiceElem = currentChoiceElem.prev();
                        }
                        break;
                    case 40: // down
                        if (currentChoiceElem.is('.choice:last-child')) {
                            currentChoiceElem = currentChoiceElem.prevAll('.choice:first-child');
                        }
                        else {
                            currentChoiceElem = currentChoiceElem.next();
                        }
                        break;
                }

            }
            currentChoiceElem.addClass('current').children('button').focus();
        });


        this.choiceList.on('mousedown keydown', '.choice button', function(e){
            if(e.type=='keydown' && e.which != 13 ) return;
            if(!self.lockAnswerStatus()) return;
            var choiceElem = $(e.target).parent('.choice');
            self.currentWord.exerciseMeta.answer.chosenIndex = choiceElem.index();

            self.highlightCorrectChoice();

            if(choiceElem.hasClass('dont-know')){
                self.failQuestion();
            }
            else if(!choiceElem.hasClass('correct')){
                self.highlightIncorrectChoice(choiceElem);
                self.failQuestion();
            }
            else{
                self.winQuestion();
            }
        });

        this.choiceList.on('mouseup mouseleave keyup', '.choice button', function(e){
            if(e.type=='keyup' && e.which != 13 ) return;
            if(!self.commitAnswerStatus()) return;
            setTimeout(self.goToNextWord.bind(self), 500);
        });

        this.choiceList.on('focus', '.choice button', this.removeChoicesOverlay.bind(this));

        this.choiceList.find('.choice button').bind('mouseover', function(e){
            self.choiceList.children('.choice').removeClass('current');
            $(e.target).focus().parent('.choice').addClass('current');
        });

    }

    overlayChoices(){
        this.overlay.show();
    }
    removeChoicesOverlay(){
        this.overlay.fadeOut('fast');
    }



    highlightCorrectChoice(){
        this.choiceList.find('.choice.correct button')
            .removeClass('btn-default')
            .addClass('btn-success');
    }
    highlightIncorrectChoice(choiceElem){
        choiceElem.children('button').removeClass('btn-default btn-secondary').addClass('btn-danger');
    }
    unhighlightChoices(){
        this.choiceList.find('.choice:not(.dont-know)')
            .children('button')
            .removeClass('btn-success btn-danger')
            .addClass('btn-default');

        this.choiceList.find('.choice.dont-know')
            .children('button')
            .removeClass('btn-danger')
            .addClass('btn-secondary');
    }

    updateAnswer(){

        this.unhighlightChoices();

        var correctAnswer = this.getAnswerText(this.currentWord);
        var choiceElems = this.choiceList.find('.choice');
        var choiceCount = choiceElems.length-1;
        var meta = this.currentWord.exerciseMeta;
        var choices = [];
        if(meta.answer){
            choices = meta.answer.choices;
        }
        else{
            var word = null;
            for(var i=0; i< choiceCount; i++){
                var augment = Math.floor((words.length)/choiceCount);
                if(augment==0) augment = 1;
                word = this.words[(this.currentWordIndex + augment*i)%this.words.length];
                choices.push(this.getAnswerText(word));
            }
            choices.sort(function(first, second){ return Math.random()-0.5;});
            meta.answer = {
                choices: choices,
                chosenIndex: false
            };
        }


        choices.forEach(function(choice, i){
            var choiceElem = choiceElems.eq(i);
            choiceElem.children('button').text(choice);
            if(choice == correctAnswer) choiceElem.addClass('correct');
            else choiceElem.removeClass('correct');

        });

        choiceElems.removeClass('current').children('button').blur();
        if(meta.status == 'committed'){
            choiceElems.children('button').addClass('disabled');
            this.removeChoicesOverlay();
            var chosenElem = choiceElems.eq(meta.answer.chosenIndex);
            this.highlightCorrectChoice();
            if(choices[meta.answer.chosenIndex] != correctAnswer){
                this.highlightIncorrectChoice(chosenElem);
            }
        }
        else{
            this.overlayChoices();
            choiceElems.children('button').removeClass('disabled');
        }


    }
}



(function($){
    $('.dictionary-view .modals .modal.external-source a').bind('click', function(e){
        $(e.target).closest('.modal').modal('hide');
    });
    $('.dictionary-view .words-list').on('click', '.word .spelling a', function(e){
        e.preventDefault();

        var spelling = $(e.target).closest('a').find('.text').text();
        var modal = $(e.target).closest('.dictionary-view').find('.modals .modal.external-source');
        modal.find('.yandex-source a').prop('href', 'https://translate.yandex.ru/?lang=en-ru&text='+encodeURIComponent(spelling));
        modal.find('.wooord-hunt-source a').prop('href', 'http://wooordhunt.ru/word/'+encodeURIComponent(spelling));
        modal.modal('show');
    });

    $('.dictionary-view .words-list').on('click', '.word .spelling .checker.package-entrance:not(.disabled)', function(changeArgs){

        var checkerElem = $(changeArgs.target).closest('.checker');

        var entrance = !checkerElem.hasClass('checked');



        checkerElem.addClass('ajax-wait disabled');
        var packageId = checkerElem.closest('.words-list').data('chosen-package-id');
        var wordId = checkerElem.closest('.word').data('word-id');
        $.post('/package/update-word-entrance', {
            packageId: packageId,
            wordId: wordId,
            entrance: entrance
        }).always(function(data, status){
            checkerElem.removeClass('ajax-wait')
                .addClass(status=='success' ? 'ajax-ok' : 'ajax-fail');
            if(status=='success') {
                checkerElem.toggleClass('checked');
            }
            setTimeout(function(){
                checkerElem.removeClass('ajax-ok ajax-fail disabled');
            },200);

        });
    });

    $('.dictionary-view .attributes-visibility-control').on('click', 'ul > li > a', function(e){
        e.preventDefault();
        var wordAttribute = $(e.target).closest('li').data('attribute');
        $(e.target).closest('.attributes-visibility-control').toggleClass(wordAttribute);
        $(e.target).closest('.dictionary-view').find('.words-list').toggleClass(wordAttribute);
    });

    $('.controls .words-order-control ul li a').bind('click', function(e){
        e.preventDefault();
        $(e.currentTarget).closest('ul').find('a').removeClass('selected');
        $(e.currentTarget).toggleClass('selected');
        window.wordExercise.orderWords(e.currentTarget.dataset.order);
    });
    $('.dictionary-view .restart-control').bind('click', function(e){
        window.wordExercise.start();
    });


    $('.dictionary-view .package-control button.add-new').bind('click', function(e){

        $(e.target).closest('.dictionary-view')
            .find('.modals .new-package.modal')
            .modal('show')
            .find('.error')
            .remove();
    });

    $('.dictionary-view .package-control .dropdown-menu li.package a.remove').bind('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        var linkElem = $(this);
        var statusElem = linkElem.find('.status');
        if(!statusElem.hasClass('ready')) return;
        statusElem.removeClass('ready').addClass('ajax-wait');
        var packageId = linkElem.closest('li.package').data('package-id');

        $.post('/package/remove/'+packageId).always(function(data, status){
            statusElem.removeClass('ajax-wait')
                .addClass(status=='success' ? 'ajax-ok' : 'ajax-fail');
            setTimeout(function(){
                if(status=='success') {
                    window.location.reload();
                }
                else{
                    statusElem.addClass('ready');
                }
            },200);

        });
    });

    $('.dictionary-view .modals .new-package.modal button.submit').bind('click', function(e){

        var formElem= $(e.target.form);
        formElem.find('.error').remove();
        formElem.find('button').attr('disabled', true);
        var formData =  formElem.serializeArray();
        var modalElem = formElem.closest('.modal');
        var statusElem = modalElem.find('.modal-footer .status');
        statusElem.removeClass('ajax-ok ajax-fail').addClass('ajax-wait');
        $.ajax({
            url: e.target.form.action,
            data: formData,
            method: 'post',
            statusCode: {
                302: function(){ console.log(302); }
            }
        }).always(function(data, status, jqXHR){
            statusElem.removeClass('ajax-wait')
                .addClass(status=='success' ? 'ajax-ok' : 'ajax-fail');
            if(status!='success') {
                data = data.responseText ? JSON.parse(data.responseText) : {};
                if(data.errors){
                    for( field in data.errors) {
                        var fieldElem = formElem.find('[name="package[_name_]"]'.replace('_name_', field));
                        fieldElem.prev('.error').remove();
                        $('<span class="error">').text(data.errors[field]).insertBefore(fieldElem);
                    }
                }
                else{
                    var errorMessage = formElem.data('request-error-message');
                    $('<span class="error">').text(errorMessage).prependTo(formElem);
                }
            }
            formElem.find('button').removeAttr('disabled');
            setTimeout(function(){
                statusElem.removeClass('ajax-ok ajax-fail');
                if(status=='success') {
                    modalElem.modal('hide');
                    //window.location.reload();
                    console.log('always status: '+jqXHR.status)
                }
            },500);

        });
    });


})(jQuery);

