var my = my || {};

$(function () {

    my.vm = function () {

        var self = this;
        self.my_fields = ko.observableArray();
        
        self.addField = function () {
            var newField = new my.QrField();
            self.my_fields.push(newField);
            self.my_fields.valueHasMutated();

            self.ajustarContadores();
        };

        self.deleteField = function () {
            self.my_fields.remove(this);

            self.ajustarContadores();
        };

        self.ajustarContadores = function () {
            var contador = 0;
            $.each(self.my_fields(), function() {
                contador++;
                this.contador(contador);
            });
            self.my_fields.valueHasMutated();
        };

        self.sendForm = function () {

            var fieldsJson = ko.toJSON(self.my_fields());
            $('#fieldsJson').val(fieldsJson);

            show_loader();
            $('#form_item').submit();
        };

      
        self.chargeData = function () {
            
            show_loader();
            
            var fieldsJson = $('#fieldsJson').val();
            if(fieldsJson!=''){
                var fields = JSON.parse(fieldsJson);
                var contador = 0;
                $.each(fields, function() {
                    var newField = new my.QrField();
                    newField.parsearJson(this);
                    contador++;
                    newField.contador(contador);
                    self.my_fields.push(newField);
                });
                self.my_fields.valueHasMutated();
            }

            hide_loader();
        };
        
        self.chargeData();

    };
    
    ko.applyBindings(my.vm);
    
});
