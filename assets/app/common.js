jQuery.validator.setDefaults({
    errorElement: "em",
    errorPlacement: function(error, element) {
        error.addClass("invalid-feedback");

        element.prop("type") === "checkbox"
            ? error.insertAfter(element.next("label"))
            : error.insertAfter(element);
    },
    highlight: function(element, errorClass, validClass) {
        $(element).addClass("is-invalid");
    },
    unhighlight: function(element, errorClass, validClass) {
        $(element).removeClass("is-invalid");
    }
});

var AS = {
    App: {
        lang: "en"
    },
    Util: {},
    Http: {}
};

AS.Util.loadingButton = function(button, loadingText) {
    button.data("original-content", button.html())
	.text(loadingText)
	.addClass("disabled")
	.attr('disabled', "disabled");
};

AS.Util.removeLoadingButton = function (button) {
    button.html(button.data("original-content"))
	.removeClass("disabled")
	.removeAttr("disabled")
	.removeAttr("rel");
};

AS.Util.displaySuccessMessage = function (parentElement, message) {
    $(".alert-success").remove();
    var div = ("<div class='alert alert-success mb-3'>"+message+"</div>");
    parentElement.prepend(div);
};


AS.Util.displayErrorMessage = function(element, message) {
    element.addClass('is-invalid').removeClass('is-valid');
    if(typeof message !== "undefined") {
        element.after(
		$("<em class='invalid-feedback' style='color:red;'>"+message+"</em>")
        );
	}
};


AS.Util.removeErrorMessages = function () {
    $("form input").removeClass('is-invalid').removeClass('is-valid');
    $(".invalid-feedback").remove();
};

AS.Util.urlParam = function(name) {
    return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)')
	.exec(location.search)||[,""])[1]
	.replace(/\+/g, '%20'))||null;
};


AS.Util.showFormErrors = function (form, error) {
	console.log(error);
    $.each(error.responseJSON.errors, function (key, error) {
        AS.Util.displayErrorMessage($(form).find("input[name="+key+"]"), error);
	});
};

AS.Util.hash = function (value) {
    return value.length ? CryptoJS.SHA512(value).toString() : "";
};


AS.Http.submit = function (form, data, success, error, complete) {
    AS.Util.removeErrorMessages();
	
    var $submitBtn = $(form).find("button[type=submit]");
	
    if ($submitBtn) {
        AS.Util.loadingButton($submitBtn, $submitBtn.data('loading-text') || "Working...");
	}
	
    $.ajax({
        url: "ajax.php",
        type: "POST",
        dataType: "json",
        data: data,
        success: function (response) {
			
			if (typeof success === "function") {
                success(response);
			}
		},
        error: error || function (errorResponse) {
            AS.Util.showFormErrors(form, errorResponse);
		},
        complete: complete || function () {
            if ($submitBtn) {
                AS.Util.removeLoadingButton($submitBtn);
			}
		}
	});
};

AS.Http.post = function (data, success, error, complete) {
    $.ajax({
        url: "ajax.php",
        type: "POST",
        dataType: "json",
        data: data,
        success: success || function () {},
        error: error || function (errorResponse) { console.log(errorResponse); },
        complete: complete || function () {}
    });
};



