var interval;
// Cast strap class into an array
var straps = document.querySelectorAll('.strap');

function changeWatchData(currentWatch) {
    // Cast attribute value to variables 
    var strapName = currentWatch.getAttribute('data-name');
    var strapPrice = currentWatch.getAttribute('data-price');
    var strapColor = currentWatch.getAttribute('data-color');

    // Insert HTML content
    document.querySelector('.data-name').innerHTML = 'Watchname: ' + strapName;
    document.querySelector('.data-price').innerHTML = 'Price:' + strapPrice + ' $';
    document.querySelector('.data-color').innerHTML = 'Description:' + strapColor;
}

for (var i = 0; i < straps.length; i++) {
    straps[i].addEventListener('click', function(e) {
        // Get attribute

        var currentWatch = e.target.getAttribute('data-value');
        $('#main-watch').fadeOut(500, function() { // hide
            $('#main-watch').attr("src", currentWatch);
            $('#main-watch').fadeIn(500); // show
        });

        changeWatchData(e.target);
    });
    //...      
}
//...


// Set a time handler that executes code every 10 seconds
interval = setInterval(function() {
    // Generate a random number between 0 and strap arrays length
    var rng = Math.random() * straps.length;
    straps[Math.floor(rng)].click();
}, 10000);


document.addEventListener('click', function(event) {
    // If click event doesn't contain classname strap stop the time handler
    if (event.target.className != 'strap') {
        clearInterval(interval);
    }
});

// Initalize variable to not contain junk value
var counter = 0;


document.getElementById('left-changer').addEventListener('click', function() {


    // Increment by one each iteration


    $('.left-strap-mobil').fadeOut(500, function() { // hide
        counter++;
        document.querySelector('.left-strap-mobil').src = 'assets/img/Omega-Studio/Strap-' + counter + '.png';
        $('.left-strap-mobil').fadeIn(500); // show
    });

    document.getElementById('left-strap-btn-mobil').setAttribute('class', 'toggle-' + counter + ' strap')
    document.getElementById('left-strap-btn-mobil').setAttribute('data-name', 'Strap-' + counter);
    document.getElementById('left-strap-btn-mobil').setAttribute('data-value', './assets/img/Omega-Studio/Watch-' + counter + '.png');

    // Reset the counter if equals 3
    if (counter == 3) {
        counter = 0;
    }
});



var rightCounter = 3;
document.getElementById('right-changer').addEventListener('click', function() {

    // Increment right counter by one each iteration


    $('#right-strap-mobil').fadeOut(500, function() { // hide
        rightCounter++;

        document.getElementById('right-strap-mobil').src = 'assets/img/Omega-Studio/Strap-' + rightCounter + '.png';
        $('#right-strap-mobil').fadeIn(500); // show
    });


    document.getElementById('right-strap-btn-mobil').setAttribute('class', 'toggle-' + rightCounter + ' strap')
    document.getElementById('right-strap-btn-mobil').setAttribute('data-name', 'Strap-' + rightCounter);
    document.getElementById('right-strap-btn-mobil').setAttribute('data-value', './assets/img/Omega-Studio/Watch-' + rightCounter + '.png');

    // Reset the counter if equals 6
    if (rightCounter == 6) {
        rightCounter = 3;
    }
});