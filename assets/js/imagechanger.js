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



interval = setInterval(function() {
    var rng = Math.random() * straps.length;
    straps[Math.floor(rng)].click();
}, 10000);


document.addEventListener('click', function(event) {
    if (event.target.className != 'strap') {
        clearInterval(interval);
    }
});

var counter = 0;


document.getElementById('left-changer').addEventListener('click', function() {
    // let straps = [1, 2, 3];
    // let rng = Math.floor(Math.random() * straps.length);

    counter++;

    document.querySelector('.left-strap-mobil').src = 'assets/img/Omega-Studio/Strap-' + counter + '.png';

    document.getElementById('left-strap-btn-mobil').setAttribute('class', 'toggle-' + counter + ' strap')
    document.getElementById('left-strap-btn-mobil').setAttribute('data-name', 'Strap-' + counter);
    document.getElementById('left-strap-btn-mobil').setAttribute('data-value', './assets/img/Omega-Studio/Watch-' + counter + '.png');

    if (counter == 3) {
        counter = 0;
    }
});



var rightCounter = 3;
document.getElementById('right-changer').addEventListener('click', function() {
    // let straps = [1, 2, 3];
    // let rng = Math.floor(Math.random() * straps.length);

    rightCounter++;

    document.getElementById('right-strap-mobil').src = 'assets/img/Omega-Studio/Strap-' + rightCounter + '.png';

    document.getElementById('right-strap-btn-mobil').setAttribute('class', 'toggle-' + rightCounter + ' strap')
    document.getElementById('right-strap-btn-mobil').setAttribute('data-name', 'Strap-' + rightCounter);
    document.getElementById('right-strap-btn-mobil').setAttribute('data-value', './assets/img/Omega-Studio/Watch-' + rightCounter + '.png');

    if (rightCounter == 6) {
        rightCounter = 3;
    }
});