/**
 * Requires jQuery
 * @author Fabrice Dant
 */
var CustomInputs = {
	Number:{
		bind: function(){
			//All input type number
			$('input[type=number]').keypress( CustomInputs.Number.typeFloat );
			//Phone && zip code: only type integer on keypress and check decimal on blur
			$('input[type=tel], input.zip_code')
				.keypress( CustomInputs.Number.typeInt )
				.blur(function(){ CustomInputs.Number.checkInteger(this); });
			//Social Security Number: auto format on blur
			$('input.ss_number').blur(function(){
				var ssnumber	= this.value.replace(/ /g, '').toUpperCase();
				var ranges		= {0:1, 1:3, 3:5, 5:7, 7:10, 10:13, 13:15};
				var newnumber	= '';
				for (var i in ranges){
					if (ranges[i]<=ssnumber.length)
						newnumber += ssnumber.substring(i, ranges[i]) + ' ';
					else{
						newnumber += ssnumber.substring(i)
						break;
					}
				}
				this.value = newnumber.trim();
			});
		},
		checkInteger: function(input){
			var sign = input.value.charAt(0) === '-' ? '-' : '';
			input.value = sign + input.value.replace(/\D/g, '');
		},
		typeInt: function(keyevt){
			var keycode = keyevt.which | keyevt.keyCode;
			if ([8,9,13,36,37,38,39,40,45,46].indexOf(keycode) > -1) return true;//8=backspace, 9=tab, 13=return
			return (keycode >= 48 && keycode <= 57);
		},
		typeFloat: function(keyevt){
			var keycode = keyevt.which | keyevt.keyCode;
			if ([8,9,13,36,37,38,39,40,46].indexOf(keycode) > -1) return true;//8=backspace, 9=tab, 13=return
			if (keycode == 45){//minus
				this.value = '-' + this.value.replace('-', '');
				return false;
			}
			return ((keycode >= 48 && keycode <= 57) || (keycode >= 44 && keycode <= 46));
		}
	},
	Date:{
		checked: false,
		statusOK: false,
		check: function(year, month, day){
			if (day.length===1) day = '0'+day;
			if (month.length===1) month = '0'+month;
			var strDate = year + '/' + month + '/' + day;
			var oDate = new Date(strDate);
			var newDayOfMonth = oDate.getDate().toString();
			if (newDayOfMonth.length===1) newDayOfMonth = '0'+newDayOfMonth;
			if (isNaN(newDayOfMonth) || newDayOfMonth!=day)
				return false;
			return true;
		},
		validation: function(form, force){
			if (CustomInputs.Date.checked && !force) return CustomInputs.Date.statusOK;
			if (force) CustomInputs.Date.checked = false;
			CustomInputs.Date.statusOK = true;
			try{
				$(form).find('select.date.day:visible').each(function(){
					if (!$(this).prop('disabled')){
						var day = this.value;
						var month = $(this).parent().find('select.month').val();
						var year = $(this).parent().find('select.year').val();
						if (!CustomInputs.Date.check(year, month, day)){
							$(this).parent().find('select').addClass('error');
							CustomInputs.Date.statusOK = false;
						}
						else
							$(this).parent().find('select').removeClass('error');
					}
				});
				$(form).find('input.custom_fr_date').each(function(){
					var $this = $(this);
					if (!$this.prop('disabled')){
						if ($this.val() != '' && !CustomInputs.Date.checkCustomFr(this)){
							$this.addClass('error');
							CustomInputs.Date.statusOK = false;
						}
						else
							$this.removeClass('error');
					}
				});
			}
			catch(exc){
				//alert(exc.message);
				CustomInputs.Date.statusOK = false;
			}
			CustomInputs.Date.checked = true;
			return CustomInputs.Date.statusOK;
		},
		/**
		 * Specific bind for custom french date input (a text with a hidden inputs)
		 */
		bindCustomFr: function(){
			$('input.custom_fr_date').
				keypress( CustomInputs.Number.typeInt ).
				keyup(function(keyevt){
					var keycode = keyevt.which | keyevt.keyCode;
					if ([8, 37, 38, 39, 40].indexOf(keycode) > -1) return;
					
					//Clear value
					$('#' + this.id + '_hidden').val('');
					//Declare
					var day = '';
					var month = '';
					var year = '';
					var cleanstring = this.value.replace(/\//g, '');
					var strlen = this.value.length;
					var cleanstrlen = cleanstring.length;
					
					var expDate = this.value.split('/');
					if (expDate.length == 3){
						day = expDate[0];
						month = expDate[1];
						year = expDate[2];
					}
					else if (expDate.length == 2){
						day = expDate[0];
						if (expDate[1].length <= 2)
							month = expDate[1];
						else{
							month = expDate[1].substring(0, 2);
							year = expDate[1].substring(2);
						}
					}
					else{
						day = cleanstring.substring(0, 2);
						month = cleanstring.substring(2, 4);
						year = cleanstring.substring(4, 8);
					}
					
					if (day.length===1 && day != '0') day = '0'+day;
					if (month.length===1 && month != '0') month = '0'+month;
					
					//Process
					if (keycode === 111){// slash key pressed
						var cursorPos = CustomInputs.getCursorPosInText(this);
						this.value = day;
						if (day != '0'){
							this.value += '/';
							if (month){
								this.value += month;
								if (month != '0'){
									this.value += '/';
									if (year)
										this.value += year;
								}
							}
						}
						if (this.value.length > strlen)
							cursorPos += (this.value.length - strlen);
						CustomInputs.setCursorPosInText(this, cursorPos);
					}
					else if (strlen === 2){
						this.value += '/';
					}
					else if (cleanstrlen === 4){
						this.value = day + '/' + month + '/';
					}
					//Last check
					if (this.value.length === 10){
						if (this.value != '' && !CustomInputs.Date.checkCustomFr(this))
							$(this).addClass('error');
						else
							$(this).removeClass('error');
					}
				}).blur(function(){
					if (this.value != '' && !CustomInputs.Date.checkCustomFr(this))
						$(this).addClass('error');
					else
						$(this).removeClass('error');
				});
		},
		checkCustomFr: function(input){
			$('#' + input.id + '_hidden').val('');
			var statusOk = true;
			//Cast Fr date to US
			var expDate = input.value.split('/');
			var year, month, day;
			if (expDate.length !== 3){
				statusOk = false;
			}
			else{
				year = expDate[2];
				month = expDate[1];
				day = expDate[0];
				if (!CustomInputs.Date.check(year, month, day)){
					statusOk = false;
				}
			}
			if (!statusOk){
				this.value = '';
			}
			else{
				if (day.length===1) day = '0'+day;
				if (month.length===1) month = '0'+month;
				$('#' + input.id + '_hidden').val(year + '-' + month + '-' + day);
			}
			return statusOk;
		},
		
	},
	
	getCursorPosInText: function(input){
		var pos = 0;
		if (document.selection){
		    var range = document.selection.createRange();
		    range.moveStart('character', -input.value.length);
		    pos = range.text.length;
		}
		else if (input.selectionStart >= 0)
			pos = input.selectionStart;
		return pos;
	},
	setCursorPosInText: function(input, pos){
		CustomInputs.setSelectionRange(input, pos, pos);
	},
	setSelectionRange: function(input, selectionStart, selectionEnd){
		if (input.setSelectionRange){
			input.setSelectionRange(selectionStart, selectionEnd);
		}
		else if (input.createTextRange){
			var range = input.createTextRange();
			range.collapse(true);
			range.moveEnd('character', selectionEnd);
			range.moveStart('character', selectionStart);
			range.select();
		}
	},
	
	onSubmit: function(){
		$('form').submit(function(){
			var ok = true;
			$(this).find('input[type=tel]:visible').each(function(){
				var tel = this.value.replace(/\D/g, '');
				if (isNaN(tel)){
					alert('Wrong phone Number');
					ok = false;
				}
				this.value = tel;
			});
			$(this).find('input.ss_number:visible').each(function(){
				var ssnumber = this.value.replace(/ /g, '').toUpperCase();
				if (ssnumber.length != 15){
					alert('Wrong Social Security Number: must have 15 numbers');
					ok = false;
				}
			});
			if ($(this).find('select.date.day:visible').length)
				if (!CustomInputs.Date.validation(this, true)) ok = false;
			return ok;
		});
	}
};

$(function(){
	//For inputs type number, zip code, social security number
	CustomInputs.Number.bind();
	//For french custom date input
	CustomInputs.Date.bindCustomFr();
	//Checks on submit form having those inputs
	CustomInputs.onSubmit();
});