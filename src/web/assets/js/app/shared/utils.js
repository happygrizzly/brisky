;(function() {

    'use strict';

    function setCustomFileInputs() {

        // http://tympanus.net/codrops/2015/09/15/styling-customizing-file-inputs-smart-way/

        var inputs = document.querySelectorAll('.input-file');
        Array.prototype.forEach.call(inputs, function(input) {

            var label = input.nextElementSibling;
            var labelVal = label.innerHTML;

            input.addEventListener('change', function(e) {
                
                var fileName = e.target.value.split('\\').pop();

                if(fileName) {
                    label.querySelector('span').innerHTML = fileName;
                }                    
                else {
                    label.innerHTML = labelVal;
                }

            });

        });
    }

    return {
        setCustomFileInputs: setCustomFileInputs
    };

})();