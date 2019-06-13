$.validator.addMethod('validPassword',
    function (value, element, param) {
        if(value !== '') {
            if(value.match(/.*[a-z]+.*/i) == null) {
                return false; // At least one letter
            }
            if(value.match(/.*\d+.*/) == null) {
                return false; // At least one number
            }
        }

        return true;
    },
    'Must contain at least one letter and one number'
);