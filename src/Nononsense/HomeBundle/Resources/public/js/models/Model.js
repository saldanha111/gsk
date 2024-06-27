var my = my || {};

$(function () {
	
    my.QrField = function () {
        var self = this;
        self.id = ko.observable("-1");
        self.name = ko.observable("");
        self.value = ko.observable("");
        self.contador = ko.observable(0);
   };
    
    my.QrField.prototype.parsearJson = function (qrField) {
        var self = this;
        self.id(qrField.id);
        self.name(qrField.name);
        self.value(qrField.value);
    };

});

