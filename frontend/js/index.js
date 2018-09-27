{
	const componentSettings = {
			UserComponent: {
				datasource: ['mysql', 'api'],
				host: 'l72.17.0.2/',
				//host: 'localhost/temp3/',
				url: {
					getUser: (sort = "ASC") => `/users/${sort}`,
					filterUser: (name, sort = "ASC") => `/search/user/${name}/${sort}`,
					saveUser: () => `/user/save`,
					deleteUser: (id) => `/user/delete/${id}`,
				}
			}
		};
	
	let users = [
		{
			id: 1,
			name: "pista",
			email: "valami@valami.hu",
			created: "2018-09-26 10:10:14"
		},
		{
			id: 2,
			name: "juci",
			email: "hurka@valami.hu",
			created: "2018-09-26 12:10:14"
		},
		{
			id: 3,
			name: "marcsa",
			email: "izemize@valami.hu",
			created: "2018-09-26 14:10:14"
		},
	]
	
	function Ajax () {

		function serialize(obj, prefix) {
			let str = [], p;
			for(p in obj) {
				if (obj.hasOwnProperty(p)) {
				  let k = prefix ? prefix + "[" + p + "]" : p, v = obj[p];
				  str.push((v !== null && typeof v === "object") ?
					serialize(v, k) :

					encodeURIComponent(k) + "=" + encodeURIComponent(v));
				}
			}
			return str.join("&");
		};

		function request (url, method, data={}, success, error) {
			if (typeof error != "function" || typeof success != "function") { return alert('Missing classback(s)....'); }
			if (!url) { return error('no settings for request'); }
			const contentType = 'application/x-www-form-urlencoded',
				httpRequest = new XMLHttpRequest(),
				timeout = 3000;
				
			if ((!data || (Object.keys(data).length === 0 && data.constructor === Object))) {
				data = null;
			} else if (method === "GET") {
				url += (~url.indexOf("?") ? "&" : "?") + serialize(data);
				data = null;
			}
			
			httpRequest.onreadystatechange = function(event) {
				if (this.readyState === 4) {
					if (this.status === 200) {
						if (!this.response) { error("no returned data"); return false; }
						if (notifyMsg) { notify.add(...notifyMsg); }
						if (!this.response.success) { return error(this.response); }
						success (this.response.data || this.response);

					} else {
						error(this.status);
					}
				}
			};

			httpRequest.responseType = 'json';
			httpRequest.open(method, url, true);

			httpRequest.timeout = timeout; // time in milliseconds
			httpRequest.ontimeout = function (e) {
				error('Time out');
			};

			if (method !== "POST" || !data) {
				httpRequest.send();
			} else {
				httpRequest.setRequestHeader('Content-Type', contentType);
				httpRequest.send(serialize(data));
			}
		}

		return {
			get(url, data=null, success=null, error=null){
				request (url, 'GET', data, success, error);
			},
			post(url, data=null, success=null, error=null){
				request (url, 'POST', data, success, error);
			},
			raw(setup, success, error){
				request (setup.url, setup.method, setup.data, success, error);
			}
		}
	}
	
	let UserComponent = (function (settings, request) {
		
		const root = document.getElementById('userRoot'),
			container = document.getElementById('userList'),
			formContainer = document.getElementById('userAddForm'),
			submitButton = document.getElementById('submitButton'),
			template = {
				container(userList) {
					return userList.map(user => template.box(user)).join('');
				},
				box(user) {
					const keys = Object.keys(user),
						id = user.id,
						rows = keys.map(key => template.row(key, user[key])).join(''),
						actions = template.link('delete', [id]);
					return `<div class="user-box" data-id=${id}>
						${rows}
						<div class="text-center mt-1">${actions}</div>
					</div>`;
				},
				row(key, value) {
					return `<div class="flex-data w-100" data-field=${key}>
						<span>${key}: </span>
						<span>${value}</span>
					</div>`;
				},
				link(action, data = []) {
					return `<button class="action_${action}" data-action="${action}User" data-id="id">${action}</button>`;
				},
				input(name, type, placeholder) {
					return `<input name="${name}" type="${type}" placeholder="${placeholder}" value="" /><br />`;
				}
			},

			handlers = {
				getUser(url) {
					request.get(url, null, renders.success, renders.error);
				},
				/*
				getId(url) {
					request.get(url, null, renders.success, renders.error);
				},
				filterUser(url) {
					request.get(url, null, renders.success, renders.error);
				},
				*/
				saveUser(url) {
					request.get(url, null, renders.success, renders.error);
				},
				deleteUser(url) {
					request.get(url, null, renders.success, renders.error);
				},
				error(data) {
					let message = data.message || "Target machine not acceasble or refused the connection!";
					alert(message);					
				}
			},
			formFields = {
				api: {
					fullname: ['text', 'Full name'],
					email: ['email', 'Email'],
					company: ['text', 'Company'],
					city: ['text', 'City'],
				},
				mysql: {
					username: ['text', 'Username'],
					name: ['text', 'Name'],
					mail: ['email', 'Email Address'],
					password: ['password', 'Password'],
				}
			};
		
		let current = {
			filter: "",
			datasource: getDatasource(),
			order: getOrder(),
		}
			
		function createForm() {
			const ds = getDatasource(),
				fields = formFields[ds] || false;
			
			if (!fields) { return; }
			formContainer.innerHTML = Object.keys(fields)
				.map(key => template.input(key, ...fields[key]))
				.join('');
		}
		
		function getDatasource() {
			return root.querySelector('input[name="datasource"]:checked').value || 'mysql';
		}
		
		function getOrder() {
			return root.querySelector('input[name="order"]:checked').value || "ASC";
		}
		
		function clickHandler(e) {
			const d = e.target.dataset, { id = null, action = null, form = null} = d;
			let data = [], method = "get";
			if (!action || !handlers[action]) { return; }
			
			if (action == "deleteUser") {
				if (!id) { return handlers.error('Missing id!'); }
				data = [id];
			} else if (action == "saveUser") {
				
			}
			
					//const ds = getDatasource(),
					//	actionKey = action+"User",
						//						url = settings.host+ds+settings.url[actionKey](...data);	
			if (form) {
				data = getFormContent(form);
				method = "post";
				e.preventDefault();
			}
			
			console.log(handlers[action], handlers.error);
			request[method](url, data, handlers[action], handlers.error);
		}
		
		function getFormContent(containerID) {
			const target = document.getElementById(containerID);
			if (!target) { return null; }
			const inputs = target.querySelectorAll('input, select, textarea');
			if (!inputs.length) { return null; }
			let data = {};
			for (const input of inputs) {
				console.log(input.name, input.value || "1");
				data[input.name] = input.value;
			}
			return data;
		}
		
		(function init() {
			container.innerHTML = template.container(users);
			root.addEventListener('click', clickHandler);
			submitButton.addEventListener('click', clickHandler);
			createForm();
		})();
		
		return {
			remove() {
				container.removeEventListener('click', clickHandler);
				submitButton.removeEventListener('click', clickHandler);				
			}
		}
	})(componentSettings.UserComponent, new Ajax);
}