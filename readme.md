![wshbr-logo](/assets/img/logo.png?raw=true "wshbr-logo")

A wordpress plugin to hook a simple review form to your posts

# Features:
- Rating
- Short review
- Provide Function for Themes
- Print out Template for Sidebar in https://github.com/Machigatta/wshbr-wordpress-theme

# Theme-Usage:
```php
//if this plugin is installed
if(class_exists("wsreview")){
    $wsr = new wsreview();
    if(!is_front_page() ){
        //draw 2 review-previews on front page
        $wsr->renderPluginAsWidget(2);
    }else {
        //draw 3 review-previews on every other page
        $wsr->renderPluginAsWidget(3);
    }
}
```

---
Build for https://github.com/Machigatta/wshbr-wordpress-theme

`Version: 2.0`