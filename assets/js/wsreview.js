jQuery(document).ready(function(){
    const slider = document.querySelector("#wsreview-slider");
    const output = document.querySelector("#wsreview-number");
    document.addEventListener('DOMContentLoaded', function () {
        output.value = slider.value;
    });

    slider.addEventListener("input", function () {
        output.value = this.value;
    });
    output.value = slider.value;
})


