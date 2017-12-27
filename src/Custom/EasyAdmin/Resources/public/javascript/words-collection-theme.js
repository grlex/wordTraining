/**
 * Created by Aspire on 16.12.2017.
 */



class WordPurifier{

    purify(word){
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
        //console.log(word, searchRange);
        searchRange.words.splice(rangeWordIndex,1);
        //console.log(word, searchRange);
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
        newItem = $(newItem).children('.spelling').val(word).parent();

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
        }
        fillList();

        self.collection.data('count', this.listItemNumber);

        if(typeof this.doneCallback == 'function') this.doneCallback();

    }
}

class WordsFileHandler {
        constructor(collection, wordsImporter){
            this.collection = collection;
            this.wordsImporter = wordsImporter;
            this.fileElem = collection.find('.words-collection-header .words-file input');

            this.waitAnimation = {
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
            this.animationIcon = this.wordsFileElem.children('button').children('.icon.wait');
            this.waitAnimation.start(this.animationIcon);
            this.wordsFileElem.removeClass('wait done fail').addClass('wait');

            this.reader.readAsText(file);
        }
        onError(evt, collection){
            this.wordsFileElem.removeClass('wait done fail').addClass('fail');
            this.waitAnimation.stop(this.animationIcon);
            self.collection.removeClass('locked');
        }
        onLoad(evt){
            var self = this;
            this.wordsImporter.importWords(evt.target.result , function(){
                self.wordsFileElem.removeClass('wait done fail').addClass('done');
                self.waitAnimation.stop(self.animationIcon);
                self.collection.removeClass('locked');
            });
        }

    }

class WordsTextHandler {
    constructor(collection, wordsImporter){
        this.collection = collection;
        this.wordsImporter = wordsImporter;

        this.modalElem = collection.find('.words-collection-header .words-text .modal');

        this.waitAnimation = {
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
        var self = this;

        var modalTextarea = this.modalElem.find('.modal-body textarea');
        this.modalElem.find('.accept').click(function(event){
            self.modalElem.modal('hide');
            self.process(modalTextarea.val());
        });
    }
    process(text){
        this.collection.addClass('locked');
        this.wordsTextElem = this.collection.children('.words-collection-header').children('.words-text');
        this.animationIcon = this.wordsTextElem.children('button').children('.icon.wait');
        this.waitAnimation.start(this.animationIcon);
        this.wordsTextElem.removeClass('wait done fail').addClass('wait');

        var self = this;
        this.wordsImporter.importWords(text , function(){
            self.wordsTextElem.removeClass('wait done fail').addClass('done');
            self.waitAnimation.stop(self.animationIcon);
            self.collection.removeClass('locked');
        });
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
        console.log('on list item remove');
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


        this.list.css('margin-top', -startFilteredIndex*2+'em');
        this.list.css('height', untilFilteredIndex*2+'em');
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

        this.newWordContainer.on('click', '.remove-word-action a', this.onRemoveWordActionClick.bind(this));
        this.newWordContainer.on('keydown', '.word .spelling', this.onWordSpellingPreEdit.bind(this));
        this.newWordContainer.on('keyup', '.word .spelling', this.onWordSpellingPostEdit.bind(this));

        this.list.on('click', '.remove-word-action a', this.onRemoveWordActionClick.bind(this));
        this.list.on('keydown', '.word .spelling', this.onWordSpellingPreEdit.bind(this));
        this.list.on('keyup', '.word .spelling', this.onWordSpellingPostEdit.bind(this));

        this.keyPressSortDelay = 500;
        this.keyPressSortTimer = false;
        this.collection.on('click', '.clear-words-action a', this.onClearWordsActionClick.bind(this));

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
        var purifiedNewSpelling = this.wordPurifier.purify(newSpelling);
        var invalid = newSpelling!==purifiedNewSpelling;
        var prevSpelling = spellingElem.data('prevSpelling');

        spellingElem.data('prevSpelling', newSpelling);

        if(newSpelling !== prevSpelling){

            var wordElem = spellingElem.parent('.word');
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


        }
        spellingElem.focus();

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
    var filterHandler = new WordsFilterHandler(collection, searchIndex);
    var listHandler = new WordsListHandler(collection, searchIndex, filterHandler);
});




//убрать появляющуюся метку ПУСТО, если мы удалили всего одно слово, а в списке еще есть слова
//Реализовать сортировку слова при его редактировании в списке
//Обновить индекс при нажатии кнопки "Очистить список"
//Может еще чего...
