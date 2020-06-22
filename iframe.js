// attach handlers once iframe is loaded
document.getElementById('ifrm').onload = function() {

    // get reference to form to attach button onclick handlers
    var form = document.getElementById('demoForm');


    //  Button 3 transfers Greeting below to Display text box above
    form.elements.button3.onclick = function() {
        var re = /[^-a-zA-Z!,'?\s]/g; // to filter out unwanted characters
        var ifrm = document.getElementById('ifrm');
        // reference to document in iframe
        var doc = ifrm.contentDocument? ifrm.contentDocument: ifrm.contentWindow.document;
        // get reference to greeting text box in iframed document
        var fld = doc.forms['iframeDemoForm'].elements['greeting'];
        var val = fld.value.replace(re, '');
        // display value in text box
        this.form.elements.display.value = 'The greeting is: ' + val;
    }
}
