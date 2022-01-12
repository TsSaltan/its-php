/**
 * API для совершения ajax запросов
 */

var tsFrame = {
	basePath: '',

	makeURI: function(uri){
		let url = tsFrame.basePath + uri;
		return url.replace(/\/\//, '/');
	},

	ajax: {
		xhr: function(){
			if (typeof XMLHttpRequest !== 'undefined') {
				return new XMLHttpRequest();  
			}
			var versions = [
				"MSXML2.XmlHttp.6.0",
				"MSXML2.XmlHttp.5.0",   
				"MSXML2.XmlHttp.4.0",  
				"MSXML2.XmlHttp.3.0",   
				"MSXML2.XmlHttp.2.0",  
				"Microsoft.XmlHttp"
			];

			var xhr;
			for(var i = 0; i < versions.length; i++) {  
				try {  
					xhr = new ActiveXObject(versions[i]);  
					break;  
				} catch (e) {
				}  
			}
			return xhr;
		},
		
		send: function(url, callback, method, data, sync){
			tsPreloader.show();

			if(typeof(data) != 'string'){
				data = tsFrame.ajax.formData(data);
			}
			
			var x = tsFrame.ajax.xhr();
			url = tsFrame.makeURI(url);
			
			if(method == 'GET'){
				url = url + (data.length ? '?' + data : '');
			}
			
			x.open(method, url, sync);
			x.onreadystatechange = function() {
				tsPreloader.hide();

				if (x.readyState == XMLHttpRequest.DONE) {
					callback(x);//.responseText)
				}
			};
			
			x.onerror = function(){
				tsPreloader.hide();
				throw new Error;
			};
			
			if (method == 'POST') {
				x.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			}
			
			x.send(data);
			return x;
		}, 
		
		formData: function(data){
			var query = [];
			for (var key in data) {
				query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
			}
			return query.join('&');
		}
	}, 
	
	/**
	 * Send query
	 * @param  string   method   GET|POST
	 * @param  string   uri      
	 * @param  string   data     a=b&c=d...
	 * @param  callable callback 
	 */
	query: function(method, uri, data, callback){
		callback = callback || function(dt){ };
		data = data || '';
		
		return tsFrame.ajax.send('/ajax/' + uri, function(res){
			var jsonResponse = JSON.parse(res.responseText);
			console.log({Query: {method: method, uri: uri, data: data}, Response: {json: jsonResponse, status: res.status}});
			callback(jsonResponse, res.status);
		}, method, data, true);
	},
	
	/**
	 * Преобразование данных формы в key=value
	 * @param  form document.getElementById('formID')
	 * @return string a=b&c=d...
	 */
	serializeForm: function(form) {
		if(typeof form == "string"){
			form = document.getElementById(form);
		}

        if (!form || form.nodeName !== "FORM") {
                return;
        }
        var i, j, q = [];
        for (i = form.elements.length - 1; i >= 0; i = i - 1) {
                if (form.elements[i].name === "") {
                        continue;
                }
                switch (form.elements[i].nodeName) {
                case 'INPUT':
                        switch (form.elements[i].type) {
                        case 'text':
                        case 'email':
                        case 'hidden':
                        case 'password':
                        case 'button':
                        case 'reset':
                        case 'submit':
                                q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                                break;
                        case 'checkbox':
                        case 'radio':
                                if (form.elements[i].checked) {
                                        q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                                }                                               
                                break;
                        }
                        break;
                        case 'file':
                        break; 
                case 'TEXTAREA':
                        q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                        break;
                case 'SELECT':
                        switch (form.elements[i].type) {
                        case 'select-one':
                                q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                                break;
                        case 'select-multiple':
                                for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
                                        if (form.elements[i].options[j].selected) {
                                                q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].options[j].value));
                                        }
                                }
                                break;
                        }
                        break;
                case 'BUTTON':
                        switch (form.elements[i].type) {
                        case 'reset':
                        case 'submit':
                        case 'button':
                                q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                                break;
                        }
                        break;
                }
        }
        return q.join("&");
	}
}

 

 

var tsPreloader = {
	_timeout: false,
	waitTime: 1100,

	show: function(){
		let preloader = document.getElementById('preloader');
		if(typeof(preloader) != 'undefined' && preloader != null){
			preloader.style.opacity = 0.5;
			preloader.style.display = 'block';
		}
	},

	hide: function(){
		if(this._timeout) return;

		this._timeout = true;
		setTimeout(function(){
			let preloader = document.getElementById('preloader');
			if(typeof(preloader) != 'undefined' && preloader != null){
				preloader.style.opacity = 0;
				preloader.style.display = 'none';
			}

			tsPreloader._timeout = false;
		}, tsPreloader.waitTime);
	}
};

/**
 * Copies a string to the clipboard. Must be called from within an event handler such as click.
 * May return false if it failed, but this is not always
 * possible. Browser support for Chrome 43+, Firefox 42+, Edge and IE 10+.
 * No Safari support, as of (Nov. 2015). Returns false.
 * IE: The clipboard feature may be disabled by an adminstrator. By default a prompt is
 * shown the first time the clipboard is used (per session).
 */
function setClipboardText(text) {
    if (window.clipboardData && window.clipboardData.setData) {
        // IE specific code path to prevent textarea being shown while dialog is visible.
        return clipboardData.setData("Text", text); 

    } else if (document.queryCommandSupported && document.queryCommandSupported("copy")) {
        var textarea = document.createElement("textarea");
        textarea.textContent = text;
        textarea.style.position = "fixed";  // Prevent scrolling to bottom of page in MS Edge.
        document.body.appendChild(textarea);
        textarea.select();
        try {
            return document.execCommand("copy");  // Security exception may be thrown by some browsers.
        } catch (ex) {
            console.warn("Copy to clipboard failed.", ex);
            return false;
        } finally {
            document.body.removeChild(textarea);
        }
    }
}