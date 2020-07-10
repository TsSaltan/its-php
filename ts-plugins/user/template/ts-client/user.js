var tsUser = {
     login: function(form){
         this.query('login', form);
     },

    register: function(form){
        this.query('register', form);
    },

    edit: function(form){
        this.query('edit', form);
    },

    query: function(action, form){
        tsResponse.clearNotify();

        var data = tsFrame.serializeForm(form);

        tsFrame.query('POST', 'user/' + action, data, function(response, status){
        	tsResponse.processResponse(response, form);
        });
    }
}

var tsResponse = {
        processResponse: function(response, form){

        if(response == 'OK'){
            //window.location.reload();
            window.location.replace('?from=auth');
        }

        if(response.message && response.code){
            switch(response.code){
                case 1:
                    this.setNotifyText(form, 'Сохранено', 'success');
                    break;

                case 2:
                    this.setNotifyText(form, 'Пароль изменён', 'success');
                    break; 

                case 3:
                    this.setNotifyText(form, 'Новый пароль: <b>' + response.message + '</b>', 'success');
                    break;        

                case 4:
                    this.setNotifyText(form, 'Сессии пользователя завершены', 'success');
                    break; 

                case 5:
                    this.setNotifyText(form, 'Пользователь удалён', 'success');
                    setTimeout(function(){
                        location.replace(tsFrame.makeURI('/dashboard/user/list'));
                    }, 1000);
                    break;                        

                case 6:
                    form.parentElement.parentElement.remove();
                    this.setNotifyText(form, 'Социальная сеть отвязана от аккаунта', 'success');
                    break;                        

                default:
                    this.setNotifyText(form, response.message);
            }
        }

        if(response.error && response.code){
            type = 'danger';
            switch(response.code){
                case 1:
                    // Авторизация не нужна
                    window.location.reload();
                    break;                

                case 9:
                    this.setNotifyText(form, 'Такой логин уже используется', type);
                    break;

                case 10:
                    this.setNotifyText(form, 'Такой e-mail уже используется', type);
                    break;

                case 11:
                    this.setNotifyText(form, 'Во время регистрации произошла ошибка', type);
                    break;            

                case 12:
                    this.setNotifyText(form, 'Не верная пара логин/пароль', type);
                    break;

                case 13:
                    this.setNotifyText(form, 'Поля заполнены некорректно', type);
                    break;

                case 14:
                    this.setNotifyText(form, 'Невозможно изменить права доступа', type);
                    break;

                case 15:
                    this.setNotifyText(form, 'Неверный текущий пароль', type);
                    break;  

                case 16:
                    this.setNotifyText(form, 'Невозможно закрыть сессии', type);
                    break;  

                case 17:
                    this.setNotifyText(form, 'Невозможно удалить пользователя', type);
                    break;   

                case 18:
                    this.setNotifyText(form, 'Регистрация отключена', type);
                    break;           

                default:
                    this.setNotifyText(form, response.error, type);
            }

            if(response.fields){
                for (var i in response.fields) {
                    form.querySelector('input[name=' + response.fields[i] + ']').parentElement.classList.add('has-error');
                }
            }
        }
     },

     clearNotify: function(){
        // Убираем поля с ошибками
        document.querySelectorAll('.has-error').forEach(function(e){ 
            e.classList.remove('has-error'); 
        });

        // Убираем сообщения с ошибками
        document.querySelectorAll('.alert').forEach(function(e){ 
            e.className = "";
            e.classList.add('alert'); 
            e.classList.add('hidden'); 
        });
     },

     setNotifyText: function(form, text, type){
        type = type || 'info';
        var notify = form.parentElement.querySelector('.alert');
        var notifyText = notify.querySelector('.text');
        notify.classList.remove('hidden');
        notify.classList.add('alert-'+type);
        notifyText.innerHTML = text;
    }
}