(function() {
    // Utility functions
    function flash(message, options) {
        var alertClass = "alert-";
        var shownFor = 1000;
        
        if (options && options.type) {
            alertClass += options.type;
        } else {
            alertClass += "success";
        }
        
        if (options && options.shownFor) {
            shownFor = options.shownFor;
        }
        
        $('.messages').html('<div class="alert ' + alertClass + '">' + message + '</div>');
        $('.alert').delay(shownFor).fadeOut(600);
        
        if (options.clear) {
            $('.page form').find('input[type="text"], textarea').val("");
        }
    }
    
    function capitalize(word) {
        return word.charAt(0).toUpperCase() + word.slice(1);
    }
    // --
    
    // Backbone.js stuff    
    var ServiceModel = Backbone.Model.extend({
        urlRoot: '/services'
    });

    var ServiceCollection = Backbone.Collection.extend({
        url: '/services',
        model: ServiceModel
    });

    var ServiceTypeModel = Backbone.Model.extend({
        urlRoot: '/service_type'
    });

    var ServiceTypeCollection = Backbone.Model.extend({
        url: '/service_type',
        model: ServiceTypeModel
    });

    var RegisterView = Backbone.View.extend({
        el: '.page',

        events: {
            'submit #register-form': 'register'
        },

        render: function() {
            var that = this;
            that.$el.html(Mustache.render($('#template-register').html(), {}));
        },

        register: function(e) {
            e.preventDefault();
        }
    });

    var HomeView = Backbone.View.extend({
        el: '.page',

        render: function() {
            var that = this;
            that.$el.html(Mustache.render($('#template-home').html(), {}));
        },
    });

    var ConnectingView = Backbone.View.extend({
        el: '.page',

        render: function() {
            var that = this;
            that.$el.html(Mustache.render($('#template-connecting').html(), {}));
        }
    });

    var ServiceView = Backbone.View.extend({
        el: '.page',

        render: function() {
            var that = this;
            that.$el.html(Mustache.render($('#template-service').html(), {
                name: "Pedro Santiago",
                service_name: "Servicio de Grua",
                phone: "787-552-1010",
                email: "pedro@gruas.com",
                distance: "2 km"
            }));
        }
    });

    var Router = Backbone.Router.extend({
        routes: {
            'register': 'register',
            'home': 'home',
            'connecting': 'connecting',
            'service/:id': 'service'
        }
    });

    var router = new Router();

    var registerView = new RegisterView();
    var homeView = new HomeView();
    var connectingView = new ConnectingView();
    var serviceView = new ServiceView();

    router.on('route:register', function() {
        registerView.render();
    });

    router.on('route:home', function() {
        homeView.render();
    });

    router.on('route:connecting', function() {
        connectingView.render();
    });

    router.on('route:service', function(id) {
        serviceView.render();
    });
    
    Backbone.history.start();
    // --
}());
