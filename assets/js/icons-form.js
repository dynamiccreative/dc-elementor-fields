"use strict";
var dceIconsForm = ($scope, $) => {
    let form = $scope[0];
    var allInput = form.querySelectorAll(".elementor-form-fields-wrapper input");
    var allTextarea = form.querySelectorAll(".elementor-form-fields-wrapper textarea");
    var allSelect = form.querySelectorAll(".elementor-form-fields-wrapper select");
    var allLabels = form.querySelectorAll(".elementor-form-fields-wrapper .elementor-field-label");
    let icon;
    let fontSize;
    let fontUnit;
    let color;
    //let elementSettings = dceGetElementSettings($scope);
    let fieldIconSize = 20;//elementSettings.field_icon_size.size;
    let labelIconSize = 20;//elementSettings.label_icon_size.size;
    let wrapper;
    let $wrapper;
    const paddingMultiplier = 1.8;
    const marginMultiplier = 0.6;
    const updateIcons = () => {
        allInput.forEach(function (field) {
            icon = jQuery(field).attr("dce-icon-render");
            if (icon) {
                wrapper = jQuery('<div class="dce-field-input-wrapper"></div>');
                jQuery(field).wrap(wrapper).parent().prepend(icon);
                $wrapper = jQuery(field).parent();
                fontSize = jQuery(field).css("font-size");
                fontUnit = fontSize.replace(/[0-9\\.]/g, "");
                fontSize = parseInt(fontSize);
                color = jQuery(field).css("color");
                $scope.find(".dce-field-input-wrapper svg").addClass("input-icons");
                if (!fieldIconSize) {
                   // jQuery(field).css("padding-left", fontSize * paddingMultiplier + fontUnit);
                    $wrapper.find("i.input-icons").css("font-size", fontSize + fontUnit);
                    $wrapper.find("svg.input-icons").css("height", fontSize + fontUnit);
                    $wrapper.find("svg.input-icons").css("width", fontSize + fontUnit);
                    $wrapper.find("i.input-icons, svg.input-icons").css("top", "50%");
                    //$wrapper.find("i.input-icons, svg.input-icons").css("margin-top", -(fontSize / 2) + fontUnit);
                    //$wrapper.find("i.input-icons, svg.input-icons").css("left", fontSize * marginMultiplier + fontUnit);
                } else {
                    //jQuery(field).css("padding-left", fieldIconSize * paddingMultiplier + "px");
                    $wrapper.find("i.input-icons, svg.input-icons").css("top", "50%");
                    //$wrapper.find("i.input-icons, svg.input-icons").css("margin-top", -(fieldIconSize / 2) + "px");
                    //$wrapper.find("i.input-icons, svg.input-icons").css("left", fieldIconSize * marginMultiplier + "px");
                }
            }
        });
        allTextarea.forEach(function (textarea) {
            icon = jQuery(textarea).attr("dce-icon-render");
            let rows = jQuery(textarea).attr("rows");
            if (icon) {
                if (!fontSize) {
                    fontSize = jQuery(textarea).css("font-size");
                    fontUnit = fontSize.replace(/[0-9\\.]/g, "");
                    fontSize = parseInt(fontSize);
                }
                wrapper = jQuery('<div class="dce-field-input-wrapper"></div>');
                jQuery(textarea).wrap(wrapper).parent().prepend(icon);
                $wrapper = jQuery(textarea).parent();
                color = jQuery(textarea).css("color");
                let paddingTop = jQuery(textarea).css("padding-top") || "5px";
                $wrapper.find("svg").addClass("input-icons");
                if (!fieldIconSize) {
                    //jQuery(textarea).css("padding-left", fontSize * paddingMultiplier + fontUnit);
                    $wrapper.find("i.input-icons").css("font-size", fontSize + fontUnit);
                    $wrapper.find("svg.input-icons").css("height", fontSize + fontUnit);
                    $wrapper.find("svg.input-icons").css("width", fontSize + fontUnit);
                    $wrapper.find("i.input-icons, svg.input-icons").css("top", paddingTop);
                    //$wrapper.find("i.input-icons, svg.input-icons").css("left", fontSize * marginMultiplier + fontUnit);
                } else {
                    //jQuery(textarea).css("padding-left", fieldIconSize * paddingMultiplier + "px");
                    $wrapper.find("i.input-icons, svg.input-icons").css("top", paddingTop);
                    //$wrapper.find("i.input-icons, svg.input-icons").css("left", fieldIconSize * marginMultiplier + "px");
                }
            }
        });
        allSelect.forEach(function (select) {
            let $select = jQuery(select);
            icon = $select.attr("dce-icon-render");
            if (icon) {
                let $icon = jQuery(icon);
                $select.before($icon);
                $wrapper = $select.parent();
                fontSize = $select.css("font-size");
                fontUnit = fontSize.replace(/[0-9\\.]/g, "");
                fontSize = parseInt(fontSize);
                color = $select.css("color");
                $icon.find("svg").addClass("input-icons");
                if (!fieldIconSize) {
                    //$select.css("padding-left", fontSize * paddingMultiplier + fontUnit);
                    $wrapper.find("i.input-icons").css("font-size", fontSize + fontUnit);
                    $wrapper.find("svg.input-icons").css("height", fontSize + fontUnit);
                    $wrapper.find("svg.input-icons").css("width", fontSize + fontUnit);
                    $wrapper.find("i.input-icons, svg.input-icons").css("top", "50%");
                    //$wrapper.find("i.input-icons, svg.input-icons").css("margin-top", -(fontSize / 2) + fontUnit);
                    //$wrapper.find("i.input-icons, svg.input-icons").css("left", fontSize * marginMultiplier + fontUnit);
                } else {
                    //$select.css("padding-left", fieldIconSize * paddingMultiplier + "px");
                    $wrapper.find("i.input-icons, svg.input-icons").css("top", "50%");
                    //$wrapper.find("i.input-icons, svg.input-icons").css("margin-top", -(fieldIconSize / 2) + "px");
                    //$wrapper.find("i.input-icons, svg.input-icons").css("left", fieldIconSize * marginMultiplier + "px");
                }
            }
        });
        allLabels.forEach(function (label) {
            icon = jQuery(label).attr("dce-icon-render");
            if (icon) {
                fontSize = jQuery(label).css("font-size");
                fontUnit = fontSize.replace(/[0-9\\.]/g, "");
                fontSize = parseInt(fontSize);
                $(icon).prependTo(label);
                if (!labelIconSize) {
                    jQuery(label)
                        .find("svg")
                        .css("height", fontSize + fontUnit);
                    jQuery(label)
                        .find("svg")
                        .css("width", fontSize + fontUnit);
                    jQuery(label)
                        .find("svg")
                        .css("margin-right", fontSize / 3 + "px");
                } else {
                    jQuery(label)
                        .find("svg")
                        .css("margin-right", fontSize / 3 + "px");
                }
            }
        });
        $scope.find(".elementor-field-label svg").addClass("label-icons");
    };
    updateIcons();
};
jQuery(window).on("elementor/frontend/init", function () {
    elementorFrontend.hooks.addAction("frontend/element_ready/form.default", dceIconsForm);
});
