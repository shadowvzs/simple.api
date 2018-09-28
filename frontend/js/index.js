{
	const componentSettings = {
			UserComponent: {
				model: 'User',
				datasource: ['mysql', 'api'],
				host: 'http://172.17.0.2/backend/',
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
						if (!this.response || !this.response.status) { 
							return error(this.response || "no returned data");  
						}
						success(this.response.data || this.response);
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
			filterInput = root.querySelector('input[name="search"]'),
			submitButton = document.getElementById('submitButton'),
			
			template = {
				container(userList) {
					return userList.map(user => template.box(user)).join('');
				},
				box(user) {
					const keys = Object.keys(user),
						id = user.id,
						rows = keys.map(key => template.row(key, user[key])).join(''),
						actions = template.link('delete', id);
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
				link(action, value = "") {
					return `<button class="action_${action}" data-action="${action}User" data-id="${value}">${action}</button>`;
				},
				input(name, type, placeholder) {
					return `<input name="${name}" type="${type}" placeholder="${placeholder}" value="" /><br />`;
				}
			},

			handlers = {
				getUser(data) {
					container.innerHTML = template.container(data);
				},
				
				getId(data) {
					container.innerHTML = template.container(data);
				},
				
				filterUser(data) {
					container.innerHTML = template.container(data);
				},
				saveUser(url) {
					request.get(url, null, renders.success, renders.error);
				},
				deleteUser(url) {
					request.get(url, null, renders.success, renders.error);
				},
				error(data) {
					let message = data.message || "Target machine not acceasble or refused the connection!";
					if (Array.isArray(message)) {
						message = message.join(', ');
					}
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
			filter: filterInput.value,
			datasource: getDatasource(),
			order: getOrder(),
		}
			
		function createForm() {
			const fields = formFields[current.datasource] || false;
			
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
		
		function userEventHandler(e) {
			let { id = null, action = null, form = null} = e.target.dataset,
				name = e.target.name,
				param = [], 
				method = "get",
				data = null;
			console.log(current );
			if (!action || !handlers[action]) { return; }
			
			if (action == "deleteUser") {
				if (!id) { return handlers.error('Missing id!'); }
				console.log(id);
				param = [id];
			} else if (action == "filterUser") {
				if (name == "datasource") {
					current.datasource = getDatasource();
					createForm();
				} else if (name == "order") {
					current.order = getOrder();
				}
				current.filter = filterInput.value;
				if (!current.filter.length) {
					action = "getUser";
					param = [ current.order ];
				} else {
					param = [ current.filter, current.order ];
				}
			}

			if (form) {
				data = {
					[settings.model]: getFormContent(form)
				};
				method = "post";
				e.preventDefault();
			}
			
			request[method](getUrl(action, param), data, handlers[action], handlers.error);
		}
		
		function getFormContent(containerID) {
			const target = document.getElementById(containerID);
			if (!target) { return null; }
			const inputs = target.querySelectorAll('input, select, textarea');
			if (!inputs.length) { return null; }
			let data = {};
			for (const input of inputs) {
				data[input.name] = input.value;
			}
			return data;
		}
		
		function getUrl(action, param=[]) {
			return settings.host+current.datasource+settings.url[action](...param);
		}

		
		(function init() {
			container.innerHTML = template.container(users);
			root.addEventListener('click', userEventHandler);
			filterInput.addEventListener('keyup', userEventHandler);
			submitButton.addEventListener('click', userEventHandler);
			createForm();
			userEventHandler( { target: filterInput } );
		})();
		
		return {
			remove() {
				container.removeEventListener('click', clickHandler);
				submitButton.removeEventListener('click', clickHandler);	
				filterInput.removeEventListener('keyup', userEventHandler);				
			}
		}
	})(componentSettings.UserComponent, new Ajax);
	
}

