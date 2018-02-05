/**
 * Created by Aspire on 16.12.2017.
 */



class WordPurifier{

    purify(word){
        // we will accept phrases
        word = word.toLowerCase().replace(/^[^a-z]+|[^a-z]+$/,'');
        if(word.length>1 && word.search(/[^a-z]/) == -1){
            return word;
        }
        return "";
    }
}

class WordsSearchIndex {
    constructor(){
        this.init();
    }
    init(){
        this.index = Object.create(null);
        this.wordCount = 0;
    }
    clearWords(){
        this.init();
    }
    addWords(words){
        for(var i=0;i<words.length;i++) this.addWord(words[i]);
    }
    setWords(words){
        this.clearWords();
        this.addWords(words);
    }
    addWord(word, purify){
        if(!word) return false;

        var firstChar  = word[0];
        var keys = Object.keys(this.index);
        var searchRange;
        if(keys.includes(firstChar)){
            searchRange = this.index[firstChar];
            if(searchRange.words.includes(word)) return false;
            searchRange.words.push(word);
            searchRange.words.sort();
            keys.sort();
        }
        else{
            searchRange = {
                offset: 0,
                words: [word]
            };
            this.index[firstChar] = searchRange;
            keys.push(firstChar);
            keys.sort();
            var prevRange = this.index[keys[keys.indexOf(firstChar)-1]];
            if(prevRange){
                searchRange.offset = prevRange.offset + prevRange.words.length;
            }

        }
        //var keys = Object.keys(this.index).sort();
        var self = this;

        keys.slice(keys.indexOf(firstChar)+1).forEach(function(key){
            self.index[key].offset++;
        });


        this.wordCount++;
        return searchRange.offset + searchRange.words.indexOf(word);

    }
    removeWord(word){

        var searchRange = this.getSearchRange(word);
        if(!searchRange){
            return false;
        }

        var rangeWordIndex = searchRange.words.indexOf(word);
        if(rangeWordIndex===-1) return false;
        searchRange.words.splice(rangeWordIndex,1);
        var firstChar = word[0];
        var keys = Object.keys(this.index).sort();
        var self = this;
        keys.slice(keys.indexOf(firstChar)+1).forEach(function(key){
            self.index[key].offset--;
        });

        this.wordCount--;
        return true;

    }
    getSearchRange(word){
        if(!word) return false;
        var firstChar  = word[0];
        return this.index[firstChar];
    }

    getWordIndex(word){
        var searchRange = this.getSearchRange(word);
        if(!searchRange) return -1;
        var rangeWordIndex = searchRange.words.indexOf(word);
        if(rangeWordIndex===-1) return -1;
        return searchRange.offset + rangeWordIndex;

    }

    getWordCount(){
        return this.wordCount;
    }

}

class WordsImporter{
    constructor(collection, searchIndex){
        this.collection = collection;
        this.list = this.collection.find('.words-list');
        this.searchIndex = searchIndex;
        this.wordPurifier = new WordPurifier();
        this.doneCallback = null;
    }

    importWords(text, doneCallback){
        if(text.trim()=="") {
            doneCallback();
            return;
        }
        this.collection.children('.collection-empty').remove();
        //
        var words = text.split(/\b/);
        words = this.filterValidWords(words);
        this.processWords(words);
        this.doneCallback = doneCallback;
    }

    filterValidWords(words){
        var validWords = Object.create(null);
        for(var i =0; i< words.length; i++){
            var word = this.wordPurifier.purify(words[i]);
            if(word) validWords[word] = true;
        }
        return Object.keys(validWords).sort();
    }

    processWords(words){

        this.wordPrototype = this.collection.data('prototype');
        this.newItems = Object.create(null);
        this.listItemNumber = this.searchIndex.getWordCount();

        var i=0;
        var self = this;

        function addWord(){
            while(i<words.length){
                var word = words[i]; i++;
                var wordIndex = self.searchIndex.addWord(word);
                if(wordIndex !== false){
                    self.createWordElement(word, wordIndex);
                    self.listItemNumber++;
                    setTimeout(addWord, 0);
                    return;
                }
            }
            self.appendWordsToList();

        }
        addWord();
    }

    createWordElement(word, offset){
        var newItem = this.wordPrototype.replace(/__name__/g, this.listItemNumber);
        newItem = $(newItem).find('input.spelling').val(word).closest('.word');

        this.newItems["+"+offset] = newItem;

    }

    appendWordsToList(){

        var self = this;
        var wordIndexes = Object.keys(self.newItems);
        var i = 0;
        function fillList(){
            while(i<wordIndexes.length){
                var wordIndex = wordIndexes[i];
                var newItem = self.newItems[wordIndex];
                wordIndex = +wordIndex;
                var elem = self.list.children().eq(wordIndex);
                if (elem.length) elem.before(newItem);
                else self.list.append(newItem);
                i++;
                if(i%200==0){
                    setTimeout(fillList,0); return;
                }
            }
            self.collection.data('count', self.listItemNumber);
            if(typeof self.doneCallback == 'function') self.doneCallback();
        }
        fillList();
    }
}

class WordsFileHandler {
        constructor(collection, wordsImporter){
            this.collection = collection;
            this.wordsImporter = wordsImporter;
            this.fileElem = collection.find('.words-collection-header .words-file input');


            var self = this;

            this.reader = new FileReader();
            this.reader.onload = this.onLoad.bind(this);
            this.reader.onerror = this.onError.bind(this);

            this.fileElem.change(function(event){
                var file = event.target.files[0];
                if (file) {
                    self.process(file);
                }
                event.target.value = "";
            });
        }
        process(file){
            this.collection.addClass('locked');
            this.wordsFileElem = this.collection.children('.words-collection-header').children('.words-file');
            this.wordsFileElem.removeClass('wait done fail').addClass('wait');

            this.reader.readAsText(file);
        }
        onError(evt, collection){
            this.wordsFileElem.removeClass('wait done fail').addClass('fail');
            self.collection.removeClass('locked');
        }
        onLoad(evt){
            var self = this;
            this.wordsImporter.importWords(evt.target.result , function(){
                self.wordsFileElem.removeClass('wait done fail').addClass('done');
                self.collection.removeClass('locked');
            });
        }

    }

class WordsTextHandler {
    constructor(collection, wordsImporter){
        this.collection = collection;
        this.wordsImporter = wordsImporter;
        this.modalElem = collection.children('.words-collection-header').find('.modal.text-import');


        var self = this;

        var modalTextarea = this.modalElem.find('.modal-body textarea');
        this.modalElem.find('.accept').click(function(event){
            self.modalElem.modal('hide');
            self.process(modalTextarea.val());
        });

        this.collection.on('click', '.words-collection-header .words-text button', this.onFromTextImportButtonClick.bind(this));
    }
    process(text){
        this.collection.addClass('locked');
        this.wordsTextElem = this.collection.children('.words-collection-header').children('.words-text');

        this.wordsTextElem.removeClass('wait done fail').addClass('wait');

        var self = this;
        this.wordsImporter.importWords(text , function(){
            self.wordsTextElem.removeClass('wait done fail').addClass('done');
            self.collection.removeClass('locked');
        });
    }

    onFromTextImportButtonClick(event){
        this.modalElem.modal('show');
    }
}


class WordsFilterHandler{
    constructor(collection, searchIndex){
        this.collection = collection;
        this.filterInput = collection.find('.words-collection-header .filter-input');
        this.list = this.collection.find('.words-list');
        this.list.on('click', '.remove-word-action', this.onListItemRemove.bind(this));
        this.searchIndex = searchIndex;

        var timer= false;
        var filterFunc = this.onFilterInputChage.bind(this);
        this.filterInput.keyup(function(){
            clearTimeout(timer);
            timer = setTimeout(filterFunc, 500);
        });
    }
    onListItemRemove(){
        if(this.list.hasClass('filtered')){
            this.list.css('height', '-=2em');
        }
    }
    onFilterInputChage(){
        var filterText = this.filterInput.val();
        this.filter(filterText);
    }

    filter(filterText, one){
        if(filterText===""){
            this.list.css('margin-top', '');
            this.list.css('height', '');
            this.list.removeClass('filtered');
            return;
        }
        var self = this;
        var searchRange = this.searchIndex.getSearchRange(filterText);
        if(!searchRange){
            this.list.css('margin-top', '');
            this.list.css('height', '0');
            this.list.removeClass('filtered');
            return;
        }
        var offset = searchRange.offset;
        var words = searchRange.words;
        var startFilteredIndex = -1, untilFilteredIndex = -1;

        for(var i=0; i< words.length; i++){
            if(this.filterWord(words[i], filterText)){
                if(startFilteredIndex==-1) startFilteredIndex = i;
                untilFilteredIndex=i;
                if(one) break;
            }
            else if(startFilteredIndex!=-1){
                break;
            }
        }
        if(startFilteredIndex>=0) {
            startFilteredIndex += offset;
            untilFilteredIndex += offset+1;
        }
        else{
            untilFilteredIndex = 0;
        }


        var listItems = this.list.children('.word');
        if(listItems.length ) {
            var listItemHeight = listItems.first().outerHeight();

            this.list.css('margin-top', -startFilteredIndex * listItemHeight + 'px');
            this.list.css('height', untilFilteredIndex * listItemHeight + 'px');
        }

        this.list.addClass('filtered');


    }
    filterWord(word, filterText){
        return word.startsWith(filterText);
    }
}


class WordsListHandler{
    constructor(collection, searchIndex, filterHandler){

        this.collection = collection;
        this.newWordContainer = collection.find('.new-word-container');
        this.list = collection.find('.words-list');
        this.searchIndex = searchIndex;
        this.filterHandler = filterHandler;
        this.wordPurifier = new WordPurifier();
        this.collection.on('click', '.add-word-action a', this.onAddWordActionClick.bind(this));


        this.collection.on('click', '.word .remove-word-action a', this.onRemoveWordActionClick.bind(this));
        this.collection.on('keydown', '.word .spelling', this.onWordSpellingPreEdit.bind(this));
        this.collection.on('keyup', '.word .spelling', this.onWordSpellingPostEdit.bind(this));


        this.keyPressSortDelay = 500;
        this.keyPressSortTimer = false;
        this.collection.on('click', '.clear-words-action a', this.onClearWordsActionClick.bind(this));
        this.collection.on('click', '.word .auto-checker', this.onAutoCheckerClick.bind(this));

        this.initSearchIndex();
    }

    initSearchIndex(){
        var self = this;
        this.list.children().each(function(index, elem){
            self.searchIndex.addWord(elem.querySelector('.spelling').value);
        });
    }

    sortWordAfterEdit(spellingElem){


        var newSpelling = spellingElem.val().trim();
        var purifiedNewSpelling = newSpelling; // we will accept phrases // this.wordPurifier.purify(newSpelling);
        var invalid = newSpelling!==purifiedNewSpelling;
        var prevSpelling = spellingElem.data('prevSpelling');

        spellingElem.data('prevSpelling', newSpelling);

        if(newSpelling !== prevSpelling){

            var nowFocusedElem = document.activeElement;

            var wordElem = spellingElem.closest('.word');
            wordElem.detach();

            var prevDuplicate = wordElem.hasClass('duplicate');
            var newDuplicate = false;
            if (!prevDuplicate)
                this.searchIndex.removeWord(prevSpelling);

            wordElem.removeClass('duplicate invalid');

            var newIndex;
            if (invalid || (newIndex = this.searchIndex.addWord(purifiedNewSpelling)) === false) {

                this.newWordContainer.prepend(wordElem);


                var name = spellingElem.attr('name');
                spellingElem.removeAttr('name').attr('data-name',name);
                if (newSpelling === "") {
                    spellingElem.attr('placeholder', prevSpelling);
                }
                else if(invalid){
                    wordElem.addClass('invalid');
                }
                else {
                    wordElem.addClass('duplicate');
                    newDuplicate = true;
                }
                this.appendEmptyLabel(); // if needed
            }
            else {
                if(newIndex >= this.list.children().length){
                    this.list.append(wordElem);
                }
                else {
                    this.list.children().eq(newIndex).before(wordElem);
                }
                var name = spellingElem.attr('data-name');
                if(name) spellingElem.attr('name', name);
                this.removeEmptyLabel(); // if present
            }
            if(newDuplicate) this.filterHandler.filter(newSpelling, true);
            else this.filterHandler.filter("");

            nowFocusedElem.focus();
        }


    }

    onWordSpellingPreEdit(event){
        var wordElem = $(event.target);
        if("string" == typeof wordElem.data('prevSpelling')) return;
        wordElem.data('prevSpelling', wordElem.val().trim());
    }
    onWordSpellingPostEdit(event){
        clearTimeout(this.keyPressSortTimer);
        this.keyPressSortTimer = setTimeout(
            this.sortWordAfterEdit.bind(this, $(event.target)),
            this.keyPressSortDelay
        );
    }
    onAddWordActionClick(event){
        if (event.preventDefault) event.preventDefault(); else event.returnValue = false;
        var newWordElem = this.newWordContainer.children('.word').find('.spelling');
        if(newWordElem.length){
            newWordElem.focus();
            return;
        }
        var numItems = this.collection.data('count') || this.list.children().length;
        //this.collection.children('.collection-empty').remove();

        var newItem = this.collection.attr('data-prototype').replace(/__name__/g,  numItems );

        newItem = $(newItem);
        this.newWordContainer.prepend(newItem);
        newItem.find('.spelling').focus();

        this.collection.trigger('easyadmin.collection.item-added');
        this.collection.data('count', ++numItems);
    }
    onRemoveWordActionClick(event){
        if (event.preventDefault) event.preventDefault(); else event.returnValue = false;

        var wordElem = $(event.target).closest('.word');
        wordElem.remove();

        this.collection.trigger('easyadmin.collection.item-deleted');
        this.appendEmptyLabel();

        var word = wordElem.find('.spelling').val();
        this.searchIndex.removeWord(word);
    }
    appendEmptyLabel(){
        if ( 0 == this.list.children().length ) {
            if(this.collection.children('.collection-empty').length==0)
                $(this.collection.attr('data-empty-collection')).appendTo(this.collection);
        }
    }
    removeEmptyLabel(){
        if (  this.list.children().length ) {
            this.collection.children('.collection-empty').remove();
        }
    }

    onClearWordsActionClick(event){
        if (event.preventDefault) event.preventDefault(); else event.returnValue = false;
        if(this.collection.children('.collection-empty').length) return;

        this.list.empty();
        $(this.collection.attr('data-empty-collection')).appendTo(this.collection);
        this.searchIndex.clearWords();
    }
    onAutoCheckerClick(event){
        var checkbox = event.target;
        if(checkbox.checked){
            $(checkbox.closest('.word-attribute')).addClass('auto');
        }
        else{
            $(checkbox.closest('.word-attribute')).removeClass('auto');
        }

    }

}

class WordsPronounceHandler{
    constructor(collection){
        this.collection = collection;
        this.collection.on('click', '.word .word-pronounce .open-file', this.onOpenPronounceFileButtonClick.bind(this));
        this.collection.on('change', '.word .word-pronounce input[type=file]', this.onPronounceFileChanged.bind(this));
        this.collection.on('click', '.word .word-pronounce .source-type select', this.onPronounceSourceChanged.bind(this));
        this.collection.on('click', '.word .word-pronounce .player .play', this.onPlayPronouncePlayerButtonClick.bind(this));
        this.collection.on('click', '.word .word-pronounce .player .stop', this.onStopPronouncePlayerButtonClick.bind(this));
        this.microphoneModalElem = collection.children('.words-collection-header').find('.modal.microphone-pronounce');
        this.microphoneModalElem.find('.accept').click(this.onModalAcceptButtonClick.bind(this));
        this.microphoneModalElem.find('.controls .record').click(this.onMicrophoneControlClick.bind(this, 'record'));
        this.microphoneModalElem.find('.controls .pause').click(this.onMicrophoneControlClick.bind(this, 'pause'));
        this.microphoneModalElem.find('.controls .resume').click(this.onMicrophoneControlClick.bind(this, 'resume'));
        this.microphoneModalElem.find('.controls .stop').click(this.onMicrophoneControlClick.bind(this, 'stop'));
        this.microphoneModalElem.find('.controls .play').click(this.onMicrophoneControlClick.bind(this, 'play'));
        this.microphoneModalElem.find('audio').bind('ended', this.onMicrophoneControlClick.bind(this, 'stop'));
        this.microphone = {
            state: 'opened',
            startTime: 0,
            recordTime: 0,
            audioElem: this.microphoneModalElem.find('audio'),
            timeElem: this.microphoneModalElem.find('.time'),
            timer: null,
            playTime: 0
        };
        //
        this.urlModalElem = collection.children('.words-collection-header').find('.modal.url-pronounce');
        this.urlModalElem.find('.accept').click(this.onModalAcceptButtonClick.bind(this));

    }

    onStopPronouncePlayerButtonClick(){
        var playerElem = $(event.target.parentElement);
        if(playerElem.hasClass('disabled')) return;
        var audioElem = playerElem.find('audio').get(0);
        if(playerElem.hasClass('playing')) {
            playerElem.removeClass('playing');
            audioElem.pause();
            audioElem.currentTime = 0;
        }
    }
    onPlayPronouncePlayerButtonClick(event){
        var playerElem = $(event.target.parentElement);
        if(playerElem.hasClass('disabled')) return;
        var audioElem = playerElem.find('audio').get(0);
        if(audioElem.src) {
            playerElem.addClass('playing');
            audioElem.play();
        }
    }
    onPronounceSourceChanged(event){
        if(event.offsetY>=0) return;
        var selectElem = event.target;
        var pronounceElem = $(selectElem).closest('.word-pronounce');
        var playerElem = pronounceElem.find('.player').addClass('disabled');
        var audioElem = playerElem.find('audio').removeAttr('src');
        pronounceElem.find('.audio-data').val('');
        /*
         const PRONOUNCE_TYPE_UNAVAILABLE = 1;
         const PRONOUNCE_TYPE_NO = 2;
         const PRONOUNCE_TYPE_INITIAL = 3;
         const PRONOUNCE_TYPE_FILE = 4;
         const PRONOUNCE_TYPE_LINK = 5;
         const PRONOUNCE_TYPE_MICROPHONE = 6;
         const PRONOUNCE_TYPE_AUTO = 7;
         */
        var sourceType = selectElem[selectElem.selectedIndex].value;
        switch(+sourceType){
            case 1:
            case 2:
            case 7:
                break;
            case 3:
                audioElem.get(0).src = audioElem.data('initialSrc');
                break;
            case 4:
                pronounceElem.find('input[type=file]').click();
                break;
            case 5:
                this.urlModalElem.find('.modal-body .invalid-message').hide();
                this.urlModalElem.data('pronounceElem',pronounceElem).modal('show');
                break;
            case 6:

                this.microphone.audioElem.removeAttr('src');
                this.microphone.timeElem.text('0:0.0');
                this.microphoneModalElem.find('.controls').removeClass().addClass('controls opened');
                this.microphoneModalElem.data('pronounceElem',pronounceElem);
                this.microphone.state = 'opened';
                this.microphoneModalElem.modal('show');
                break;

        }
    }

    onOpenPronounceFileButtonClick(event){

        var fileInput = event.target.parentElement.querySelector('input[type=file]');
        $(fileInput).click();
    }
    onPronounceFileChanged(event){
        var pronounceElem = $(event.target).closest('.word-pronounce');
        var file = event.target.files[0];
        if(!file) return;
        var player = pronounceElem.find('.player > audio').get(0);
        var reader = new FileReader();
        reader.onload = function(e) {
            player.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    onModalAcceptButtonClick(event){
        var modal = $(event.target).closest('.modal');
        var pronounceElem = modal.data('pronounceElem');
        if(modal.hasClass('microphone-pronounce')){
            var src = this.microphone.audioElem.get(0).src;
            if(src) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var base64 = reader.result.substr('data:audio/wav;base64,'.length);
                    pronounceElem.find('.audio-mic').val(base64);
                    pronounceElem.find('.player').removeClass('disabled')
                        .find('audio').prop('src', reader.result);
                };
                reader.readAsDataURL(this.microphone.audioElem.data('wav-data'));
            }
            modal.modal('hide');
        }
        if(modal.hasClass('url-pronounce')){
            var url = modal.find('textarea').val();
            console.log(url);
            if(this.isValidURL(url)){
                pronounceElem.find('.player').find('audio').prop('src', url);
                pronounceElem.find('.audio-url').val(url);
                modal.modal('hide');
            }
            else{
                modal.find('.modal-body .invalid-message').slideUp();
                modal.find('.modal-body .invalid-message').slideDown();
            }
        }

    }
    isValidURL(url){
        try {
            var url = new URL(url);
            return true;
        }
        catch (e) {
            // Malformed URI
            console.log(e.message);
            return false;
        }
    }

    onMicrophoneControlClick(action){
        var state = this.microphone.state;
        switch(state){
            case 'opened':
                if(action == 'record') { this.record(); state = 'recording'; }
                break;
            case 'recording':
                if(action == 'stop') { this.stopRecording(); state = 'stopped'; }
                if(action == 'pause') { this.pauseRecording(); state = 'paused'; }
                break;
            case 'paused':
                if(action == 'resume') { this.resumeRecording(); state = 'recording'; }
                if(action == 'stop') { this.stopRecording(); state = 'stopped'; }
                break;
            case 'stopped':
                if(action == 'record') { this.record(); state = 'recording'; }
                if(action == 'play') { this.play(); state = 'playing'; }
                break;
            case 'playing':
                if(action == 'stop') { this.stopPlaying(); state = 'stopped'; }
                break;
        }
        this.microphone.state = state;
        this.microphoneModalElem.find('.controls').removeClass().addClass('controls').addClass(state);
    }

    formatTime(time){
        var minutes = Math.floor(time/60000);
        var seconds = Math.floor((time%60000)/1000);
        var decimals = Math.floor((time%1000)/100);
        return minutes+':'+seconds+'.'+decimals;
    }
    displayPlayTime(time){
        if(!time) time = Date.now() - this.microphone.startTime;
        var recordTime = this.microphone.recordTime;
        this.microphone.timeElem.text(this.formatTime(time)+' / '+this.formatTime(recordTime));
    }

    displayRecordTime(time){
        if(!time) time = Date.now() - this.microphone.startTime + this.microphone.recordTime;
        this.microphone.timeElem.text(this.formatTime(time));
    }
    stopRecordTimer(){
        if(this.microphone.timer) {
            clearInterval(this.microphone.timer);
            this.microphone.timer = false;
            this.microphone.recordTime += Date.now() - this.microphone.startTime;
        }
        this.displayRecordTime(this.microphone.recordTime);
    }
    startRecordTimer(resume){
        if(!resume) this.microphone.recordTime = 0;
        this.microphone.startTime = Date.now();
        this.microphone.timer = setInterval(this.displayRecordTime.bind(this),100);
    }
    record(){
        var self = this;
        if (navigator.mediaDevices.getUserMedia) {
            var userMedia = navigator.mediaDevices.getUserMedia({audio: true});
            userMedia.then(this.onMicrophoneAccessSuccess.bind(this))
                .then(function(){
                    self.startRecordTimer(false);
                });
            userMedia.catch(this.onMicrophoneAccessFail.bind(this));
        } else {
            console.log('navigator.mediaDevices.getUserMedia not present');
        }

    }
    pauseRecording(){
        this.stopRecordTimer();
        this.recorder.stop();
    }
    resumeRecording(){
        this.startRecordTimer(true);
        this.recorder.record();
    }
    stopRecording(){
        this.stopRecordTimer();
        var self = this;
        this.recorder.stop();
        this.recorder.exportWAV(function(wavData) {
            var audio = self.microphone.audioElem.get(0);
            audio.src = window.URL.createObjectURL(wavData);
            self.microphone.audioElem.data('wav-data', wavData);
            self.recorder.clear();
            self.recorder.track.stop();
        });
    }
    play(){
        var audio = this.microphone.audioElem.get(0);
        audio.play();
        this.microphone.startTime = Date.now();
        this.microphone.timer = setInterval(this.displayPlayTime.bind(this),100);
    }
    stopPlaying(){
        var audio = this.microphone.audioElem.get(0);
        audio.pause();
        audio.currentTime = 0;
        clearInterval(this.microphone.timer);
        this.displayRecordTime(this.microphone.recordTime);
    }
    onMicrophoneAccessFail(e) {
        console.log('Rejected!', e);
    }

    onMicrophoneAccessSuccess(stream) {
        var context = new AudioContext();
        var mediaStreamSource = context.createMediaStreamSource(stream);
        this.recorder = new Recorder(mediaStreamSource);
        this.recorder.record();
        this.recorder.track = stream.getTracks()[0];
    }
}


class WordsFormHandler{
    constructor(collection){
        this.collection = collection;
        this.form = this.collection.closest('form');
        this.form.submit(this.onFormSubmit.bind(this));
        this.audioUploadModalElem = collection.children('.words-collection-header').find('.modal.audio-upload');
    }
    onFormSubmit(event){
        self = this;
        var formElem = event.target;
        event.preventDefault();
        var boundary = "---------------------------7fb75az98dzz0";
        var multipartData = [];

        function addPart(elem){
            if(elem.name=='') return Promise.resolve();
            return new Promise(function(resolve, reject){
                var part = 'Content-Disposition: form-data; name="_name_"'.replace('_name_', elem.name);
                var $elem = $(elem);
                if($elem.hasClass('audio-file') && $elem.data('blob') instanceof Blob){
                    part += '; filename="audio.tmp"\r\n\r\n';
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        part +=  e.target.result;
                        part += '\r\n';
                        multipartData.push(part);
                        resolve();
                    };
                    reader.readAsText($elem.data('blob'));
                }
                else{
                    part += '\r\n\r\n';
                    part += elem.value;
                    part += '\r\n';
                    multipartData.push(part);
                    resolve();
                }
            });
        }
        var inputElems = this.form.find(':input').toArray();
        Promise.all(inputElems.map(addPart))
            .then(function(){
                var data = '--' + boundary + '\r\n';
                data += multipartData[0];
                for(var i=1;i<multipartData.length;i++){
                    data += boundary + '\r\n';
                    data += multipartData[i];
                }
                data += boundary + '--';
                self.sendForm(data);
            });

    }
    sendForm(data)
    {
        var url = this.form.get(0).action;
        $.ajax({
            url: url,
            data: data,
            method: 'post',
            contentType: 'multipart/form-data',
            dataType: 'html',
            success: function (data, textStatus, jqXHR) {
                var status = jqXHR.status;
                if (status >= 300 && status < 400) {
                    window.href.location = jqXHR.getResponseHeader('Location');
                }
                else {
                    /*var frame = $('<iframe src="javascript:false">').get(0);
                    frame.contentWindow.document.write(jqXHR.responseText)*/
                    var bodyHtml = data.substring(data.indexOf('<body>'), data.indexOf('</body>'));
                    document.body.innerHTML = bodyHtml;
                }
            },
            error: console.log
        });
    }
}

/*
class WordsCollection{
    constructor(collection){
        this.collection = collection;
        this.searchIndex = new WordsSearchIndex();
        this.fileHandler = new WordsFileHandler(collection, this.searchIndex);
        this.filterHandler = new WordsFilterHandler(collection, this.searchIndex);
        this.listHandler = new WordsListHandler(collection, this.searchIndex, this.filterHandler);
    }
}*/

$('.words-collection').each(function(index, collection) {
    collection = $(collection);

    var searchIndex = new WordsSearchIndex();
    var wordsImporter = new WordsImporter(collection, searchIndex);
    var fileHandler = new WordsFileHandler(collection, wordsImporter);
    var textHandler = new WordsTextHandler(collection, wordsImporter);
    var pronounceHandler = new WordsPronounceHandler(collection);
    //var formHandler = new WordsFormHandler(collection);
    var filterHandler = new WordsFilterHandler(collection, searchIndex);
    var listHandler = new WordsListHandler(collection, searchIndex, filterHandler);
});




//убрать появляющуюся метку ПУСТО, если мы удалили всего одно слово, а в списке еще есть слова
//Реализовать сортировку слова при его редактировании в списке
//Обновить индекс при нажатии кнопки "Очистить список"
//Может еще чего...
