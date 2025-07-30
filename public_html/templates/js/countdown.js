$('textarea').on('keyup', function () {

  var characterCount = $(this).val().length,
    current = $('#current'),
    maximum = $('#maximum'),
    theCount = $('#the-count');

  current.text(characterCount);


  if (characterCount < 100) {
    current.css('color', '#666');
  }
  if (characterCount > 99 && characterCount < 140) {
    current.css('color', '#6d5555');
  }
  if (characterCount > 139 && characterCount < 200) {
    current.css('color', '#793535');
  }
  if (characterCount > 199 && characterCount < 250) {
    current.css('color', '#841c1c');
  }
  if (characterCount > 249 && characterCount < 299) {
    current.css('color', '#8f0001');
  }

  if (characterCount >= 300) {
    maximum.css('color', '#8f0001');
    current.css('color', '#8f0001');
    theCount.css('font-weight', 'bold');

  } else {
    maximum.css('color', '#666');
    theCount.css('font-weight', 'normal');
  }


});

/*
< script src = "https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js" ></script >
<textarea name="Inhalt" id="Inhalt" placeholder="Ihre Nachricht..." rows="5" maxlength="300"></textarea>
<div id="the-count">
 <span id="current">0</span>
 <span id="maximum">/ 300</span>
</div>
*/
