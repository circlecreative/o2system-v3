function displayResults(result, div) {
    html = ["Address: " + result.address()]
    html.push("Latitude: " + result.lat())
    html.push("Longitude: " + result.lng())
    html.push("Long names:")
    result.addressTypes().forEach(function(type) {
      html.push("  " + type + ": " + result.nameForType(type))
    })
    html.push("Short names:")
    result.addressTypes().forEach(function(type) {
      html.push("  " + type + ": " + result.nameForType(type, true))
    })
    div.html( html.join('\n'));
  }

  $( function() {
    var iMapSearch = $('.map-search');
    var iMapSearchAddress = iMapSearch.attr('map-address');
    var iMapSearchResult = iMapSearch.attr('map-result');
    var addressPicker = new AddressPicker();
    $('.map-search').typeahead(null, {
      displayKey: 'description',
      source: addressPicker.ttAdapter()
    });
    addressPicker.bindDefaultTypeaheadEvent($('#'+iMapSearchAddress))
    $(addressPicker).on('addresspicker:selected', function (event, result) { displayResults(result, $('#'+iMapSearchResult))})
    $(addressPicker).on('addresspicker:predictions', function(event, result) {
      if (result && result.length > 0)
        $('.map-search').removeClass("map-not-found")
      else
        $('.map-search').addClass("map-not-found")
    })
  })

// Map Picker
$( function() {
  var iMapPicker = $('.map-picker');
  var iMapPickerID = iMapPicker.attr('id');
  var iMapData = new Object();
  var addresspicker;

  // Backup Data
  iMapBackup = $('#'+iMapPickerID+'-input').val();
  if(typeof iMapBackup != 'undefined') {
    $('#'+iMapPickerID+'-input').attr('map-backup', iMapBackup);
    iMapBackup = iMapBackup.replace(/'/gi,'"');
    console.log(iMapBackup);
    iMapBackup = JSON.parse(iMapBackup);
  }

  // instantiate the addressPicker suggestion engine (based on bloodhound)
  addressPicker = new AddressPicker({map: {id:'#'+iMapPickerID+'-map',zoom:17,center: new google.maps.LatLng(iMapBackup.latitude,iMapBackup.longitude),}, marker: {draggable: true, visible: true}, zoomForLocation: 18, reverseGeocoding: true});

  $('.map-picker').on('click','.map-picker-restore', function(){
    var iMapRestore = $('#'+iMapPickerID+'-input').attr('map-backup');
    iMapRestore = iMapRestore.replace(/'/gi,'"');
    console.log(iMapRestore);
    iMapRestore = JSON.parse(iMapRestore);
    addressPicker = new AddressPicker({map: {id:'#'+iMapPickerID+'-map',zoom:17,center: new google.maps.LatLng(iMapRestore.latitude,iMapRestore.longitude),}, marker: {draggable: true, visible: true}, zoomForLocation: 18, reverseGeocoding: true});

    $('#'+iMapPickerID+'-search').val(iMapRestore.address);
    $('#'+iMapPickerID+'-full-address').html(iMapRestore.address);
    $('#'+iMapPickerID+'-latitude').html(iMapRestore.latitude);
    $('#'+iMapPickerID+'-longitude').html(iMapRestore.longitude);
  });

  // instantiate the typeahead UI
  $('#'+iMapPickerID+'-search').typeahead(null, {
    displayKey: 'description',
    source: addressPicker.ttAdapter()
  });

  // Bind some event to update map on autocomplete selection
  $('#'+iMapPickerID+'-search').bind("typeahead:selected", addressPicker.updateMap);
  $('#'+iMapPickerID+'-search').bind("typeahead:cursorchanged", addressPicker.updateMap);

  $(addressPicker).on('addresspicker:selected', function (event, result) {
    console.log(result);
    $('#'+iMapPickerID+'-full-address').html(result.address());
    $('#'+iMapPickerID+'-latitude').html(result.lat());
    $('#'+iMapPickerID+'-longitude').html(result.lng());

    // Build JSON
    iMapData['address'] = result.address();
    iMapData['latitude'] = result.lat();
    iMapData['longitude'] = result.lng();
    iMapData = JSON.stringify(iMapData);

    $('#'+iMapPickerID+'-input').val(iMapData);

    if (result.isReverseGeocoding()) {
      $('#'+iMapPickerID+'-search').val(result.address());

      // Re-Build JSON
      iMapData['address'] = result.address();
      iMapData['latitude'] = result.lat();
      iMapData['longitude'] = result.lng();
      iMapData = JSON.stringify(iMapData);

      $('#'+iMapPickerID+'-input').val(iMapData);
    }
  });
});