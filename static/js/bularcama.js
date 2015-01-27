(function(){
	var myCounter = 0,
		dataAccumulator = {};

	function Bularcama(cfg){
		this.registerClick();
		this.templates = {};
		this.cfg = cfg || {}
	}

	Bularcama.prototype.ajaxRetries = 3;
	Bularcama.prototype.registerClick = function(){
		document.body.addEventListener('click', this._onClick(), false);
		window.addEventListener('popstate', this._onPopState(), false);
	};

	Bularcama.prototype._onPopState = function(){
		var that = this;
		return function(ev){
			that.onPopState(ev, this);
		}
	};

	Bularcama.prototype.onPopState = function(ev){
		var data = ev.state;
		this._loadPage(data);
	};

	Bularcama.prototype._onClick = function(){
		var that = this;
		return function(ev){
			try{
				for(var ele=event.target; ele != this; ele = ele.parentNode){
					if(that.onClick(ev, ele, this))
						return;
				}
			} catch(e){}
		}
	};

	Bularcama.prototype.onClick = function(ev, ele){
		switch(ele.getAttribute('data-click')){
			case 'link':
				ev && ev.preventDefault();
				var href = this.normalizeHref(ele.href || ele.getAttribute("href"));
				this.loadPage(href);
				break;

			default:
				return false;
				break;
		}

		return true;
	};

	Bularcama.prototype.normalizeHref = function(href){
		return href;
	};

	Bularcama.prototype._loadPage = function(link){
		var split = (link || "index.html").split('/'),
			file = split[split.length-1],
			name = file.split('.');

		if(name[1].toLowerCase() != "html")
			console.log("warning: trying to load page that isn't html!");

		var template = split.slice(0, -1),
			data = split.slice(0, -1);

		template.push(name[0]+".template");
		template = template.join("/");
		data.push(name[0]+".json");
		data  =data.join("/");

		if(typeof(this.cfg.loaderBegin) == "function")
			this.cfg.loaderBegin(this, id, dataAccumulator)

		var id = myCounter++;
		this.loadTemplate(template, id);
		this.loadData(data, template, id);
	};

	Bularcama.prototype.loadPage = function(link){
		history.pushState(link, "", link);
		this._loadPage(link);
	};

	Bularcama.prototype.loadData = function(page, template, id){
		new Ajax({
			url: page,
			success: (function(data){
				dataAccumulator["data_"+id] = JSON.parse(data);
				this.loadPageCb(template, id);
			}).bind(this),
			failure: (function(){
				this.loadPageFailureCb(template, id, page);
			}).bind(this)
		});
	};

	Bularcama.prototype.loadTemplate = function(template, id){
		if(this.templates[template])
			return this.loadPageCb(template, id);

		new Ajax({
			url: template,
			success: (function(data){
				this.templates[template] = JSON.parse(data);
				this.loadPageCb(template, id);
			}).bind(this),
			failure: (function(){
				this.loadPageFailureCb(template, id);
			}).bind(this)
		});
	};

	Bularcama.prototype.loadPageCb = function(template, id){
		if(typeof(dataAccumulator["data_"+id]) != "undefined" && this.templates[template]){
			if(typeof(this.cfg.processTemplate) == "function")
				this.cfg.processTemplate(this.templates[template], dataAccumulator["data_"+id]);
			delete dataAccumulator["data_"+id];

			if(typeof(this.cfg.loaderEnd) == "function")
				this.cfg.loaderEnd(this, id, dataAccumulator)
		}
	};

	Bularcama.prototype.loadPageFailureCb = function(template, id, page){
		if(page){
			if(typeof(dataAccumulator["error_page_"+id]) == "undefined")
				dataAccumulator["error_page_"+id] = this.ajaxRetries;

			if(--dataAccumulator["error_page_"+id])
				this.loadData(page, template, id);

		}else{
			if(typeof(dataAccumulator["error_template_"+id]) == "undefined")
				dataAccumulator["error_template_"+id] = this.ajaxRetries;

			if(--dataAccumulator["error_template_"+id])
				this.loadTemplate(template, id);
		}

		if( (
				typeof(dataAccumulator["error_template_"+id]) != "undefined" && !dataAccumulator["error_template_"+id] &&
				typeof(dataAccumulator["error_page_"+id]) != "undefined" && !dataAccumulator["error_page_"+id]
			) || (
				typeof(dataAccumulator["error_template_"+id]) != "undefined" && !dataAccumulator["error_template_"+id] &&
				typeof(dataAccumulator["data_"+id]) != "undefined"
			) || (
				typeof(dataAccumulator["error_page_"+id]) != "undefined" && !dataAccumulator["error_page_"+id] &&
				this.templates[template]
			)
		){
			this.cfg.processTemplate();
			if(typeof(this.cfg.loaderEnd) == "function")
				this.cfg.loaderEnd(this, id, dataAccumulator);

			delete dataAccumulator["error_template_"+id];
			delete dataAccumulator["error_page_"+id];
			delete dataAccumulator["data_"+id];
		}
	};

	window.Bularcama = Bularcama;
})();
