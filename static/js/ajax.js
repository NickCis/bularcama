/** Clase Simple para Ajax.
 * Autor: Nicolas Cisco ( http://github.com/nickcis )
 * Licencia: Gplv2
 */
(function(){
	function objToString(obj){
		if(typeof(obj) == 'string')
			return obj;

		var string = [];
		for(var key in obj){
			if(! obj.hasOwnProperty(key))
				continue;

			var ele = obj[key],
				k = encodeURIComponent(key);
			if(ele instanceof Array)
				for(var i=0, e;typeof(e = ele[i]) != 'undefined'; i++)
					string.push(k+"[]="+encodeURIComponent(e));
			else
				string.push(k+"="+encodeURIComponent(ele));
		}

		return string.join("&");
	}

	/** Clase para enviar ajax
	 * @param args {
	 *     method: metodo del pedido (defecto: get)
	 *     postHeader: cabecera para el pedido de post (defecto: application/x-www-form-urlencoded)
	 *     startLoader: llamada cuando se arranca un loader (se usa call, this es el objeto de ajax)
	 *     endLoader: llamada cuando se termina un loader (se usa call, this es el objeto de ajax)
	 *     success: 
	 *     failure:
	 *     url:
	 * }
	 * @param nope true para evitar que se enviee automaticamente el ajax request (defecto: false)
	 */
	function Ajax(args, nope){
		this.c = args || {};
		(this.c.method) || (this.c.method = 'get');
		(this.c.postHeader) || (this.c.postHeader = 'application/x-www-form-urlencoded');
		(typeof(this.c.startLoader) == "function") || (this.c.startLoader = function(){});
		(typeof(this.c.endLoader) == "function") || (this.c.endLoader = function(){});
		(typeof(this.c.success) == "function") || (this.c.success = function(){});
		(typeof(this.c.failure) == "function") || (this.c.failure = function(){});
		this.xhr = new XMLHttpRequest();
		//this.xhr.addEventListener("progress", this.updateProgress, false);
		if(typeof(this.c.progress) == 'function')
			this.xhr.upload.addEventListener("progress", this.c.progress.bind(this), false);
		/*this.xhr.addEventListener("load", this.transferComplete, false);
		this.xhr.addEventListener("error", this.transferFailed, false);
		this.xhr.addEventListener("abort", this.transferCanceled, false);*/
		this.xhr.onreadystatechange = this.onReadyStateChange.bind(this);

		nope || this.send();
	}

	/** Envia el pedido.
	 * Hace el open y el send del XHR.
	 */
	Ajax.prototype.send = function(){//Open and send
		this.c.startLoader.call(this);
		var data = null,
			header = null,
			method = "GET";
		switch(this.c.method.toLowerCase()){
			case 'get':
				method = "GET";
				if(this.c.data)
					this.c.url += ( (this.c.url.indexOf('?') == -1 )? '?' : '&') + objToString(this.c.data);
				break;

			case 'post':
				method = "POST";
				header = this.c.postHeader;
				if(this.c.data){
					switch(this.c.postHeader){
						case 'application/x-www-form-urlencoded':
						default:
							data = objToString(this.c.data);
							break;
					}
				}
				break;

			case 'form-post':
				method = "POST";
				//header = "multipart/form-data";
				data = this.c.data;
				break;

			default:
				break;
		}
		this.xhr.open(method, this.c.url, true);
		if(header)
			this.xhr.setRequestHeader("Content-type", header);
		this.xhr.send(data);
	};

	Ajax.prototype.onReadyStateChange = function(){
		if(this.xhr.readyState === 4){
			this.c.endLoader.call(this);
			if(this.xhr.status == 200)
				this.c.success.call(this, this.xhr.responseText);
			else
				this.c.failure.call(this, this.xhr.responseText);
		}
	};

	/*Ajax.prototype.updateProgress = function(evt){console.log(arguments)};
	Ajax.prototype.transferComplete = function(evt){};
	Ajax.prototype.transferFailed = function(evt){};
	Ajax.prototype.transferCanceled = function(evt){};*/


	Ajax.serialize = function (form, string) {
		if (!form || form.nodeName !== "FORM") {
			return;
		}
		var i, j, q = [], ret = {};
		function addVal(name, val){
			if(ret[name] === undefined)
				return ret[name] = val;
			else{
				if(! (ret[name] instanceof Array))
					ret[name] = [ ret[name] ];
				//ret[name].push(val);
				ret[name].unshift(val);
			}
		}
		for (i = form.elements.length - 1; i >= 0; i = i - 1) {
			if (form.elements[i].name === "") {
				continue;
			}
			switch (form.elements[i].nodeName) {
				case 'INPUT':
					switch (form.elements[i].type) {
						case 'text':
						case 'number':
						case 'hidden':
						case 'password':
						case 'button':
						case 'reset':
						case 'submit':
							q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
							//ret[form.elements[i].name] = form.elements[i].value;
							addVal(form.elements[i].name, form.elements[i].value);
							break;
						case 'checkbox':
						case 'radio':
							if (form.elements[i].checked) {
								q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
								//ret[form.elements[i].name] = form.elements[i].value;
								addVal(form.elements[i].name, form.elements[i].value);
							}
							break;
					}
					break;
				case 'file':
					break; 
				case 'TEXTAREA':
					q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
					//ret[form.elements[i].name] = form.elements[i].value;
					addVal(form.elements[i].name, form.elements[i].value);
					break;
				case 'SELECT':
					switch (form.elements[i].type) {
						case 'select-one':
							q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
							//ret[form.elements[i].name] = form.elements[i].value;
							addVal(form.elements[i].name, form.elements[i].value);
							break;
						case 'select-multiple':
							for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
								if (form.elements[i].options[j].selected) {
									q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].options[j].value));
									//ret[form.elements[i].name] = form.elements[i].value;
									addVal(form.elements[i].name, form.elements[i].value);
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
							//ret[form.elements[i].name] = form.elements[i].value;
							addVal(form.elements[i].name, form.elements[i].value);
							break;
					}
					break;
			}
		}
		return (string)? q.join("&") : ret;
	};

	window.Ajax = Ajax;
})();
