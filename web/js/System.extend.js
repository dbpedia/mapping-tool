/**
 * extend String with a
 * trim method
 */
String.prototype.trim = function(){
  return this.replace(/^\s+|\s+$/, '');
};

/**
 * repeat a String n times
 */
String.prototype.repeat = function(l){
	return new Array(l+1).join(this);
};

/**
 * extend Array with ascending
 * sort method
 */
Array.prototype.sortAsc = function(){
  return this.sort(App.charOrdAsc);
}

/**
 * extend Array with descending
 * sort method
 */
Array.prototype.sortDesc = function(){
  return this.sort(App.charOrdDesc);
}

/**
 * removes duplicate values
 * from an array
 *
 * @return array arr
 */
Array.prototype.unique = function(){
  if(this instanceof Array){
    var assoc_obj = {};
    var tmpArr = [];
    var arrLength = this.length;
    for(var i=0; i < arrLength; i++){
      /*
      if(!assoc_obj[thi[i]+typeof this[i]]){
        tmpArr.push(this[i]);
        assoc_obj[this[i]+typeof this[i]]=true;
      }
      */
      if(!assoc_obj[this[i]]){
        tmpArr.push(this[i]);
        assoc_obj[this[i]]=true;
      }
    }
    delete(assoc_obj);
  }
  return tmpArr || this;
}

/**
 * Returns true if the passed value is found in the
 * array. Returns false if it is not.
 */
Array.prototype.inArray = function (value){
  var i;
  for (i=0; i < this.length; i++) {
    // Matches identical (===), not just similar (==).
    // @TODO this[i] ??
    if(this === ''){
        return true;
    }
    if ((this[i]).toLowerCase() === value.toLowerCase()) {
      return true;
    }
  }
  return false;
};