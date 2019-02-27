jQuery(document).ready(function(){
    const slider = document.querySelector("#wsreview-slider");
    const output = document.querySelector("#wsreview-number");
    if(slider != null){
        slider.addEventListener("input", function () {
            output.value = this.value;
        });
        output.value = slider.value;
    }    
})


