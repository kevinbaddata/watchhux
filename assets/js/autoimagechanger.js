    // List of CSS classes to switch between 
    var colorText = [
        'title-2',
        'title-3',
        'title-1',
    ];

    // Store image locations in array to set src attribute of an image
    var images = [
        './assets/img/Rolex/Title-2.png',
        './assets/img/Rolex/Title-3.png',
        './assets/img/Rolex/Title-1.png',
    ];


    var length = colorText.length;
    var timer = 2000;
    var counter = 0;

    // Executes a code snippet inside body every 2 seconds
    setInterval(function() {


        // Increment value by one each iteration
        counter++;

        isContainsClass(counter);


        // Reset if counter equals 3
        if (length === counter) {
            counter = 0;
            // ...
        }
        /// ...

        console.log("Length: " + length);
        console.log("Counter: " + counter);


    }, timer);



    function isContainsClass(counter) {
        if (document.getElementById('rolex-text').classList.contains(colorText[0])) {
            document.getElementById('rolex-text').classList.remove(colorText[0]);
        } else if (document.getElementById('rolex-text').classList.contains(colorText[1])) {
            document.getElementById('rolex-text').classList.remove(colorText[1]);
        } else if (document.getElementById('rolex-text').classList.contains(colorText[2])) {
            document.getElementById('rolex-text').classList.remove(colorText[2]);
        }

        document.getElementById('rolex-text').classList.add(colorText[counter - 1]);
        document.getElementById('rolex-img').src = images[counter - 1];

    }