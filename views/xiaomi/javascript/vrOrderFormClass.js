/**
 * 订单对象
 * address:收货地址; delivery:配送方式; payment:支付方式;
 */
function orderFormClass()
{
	_self = this;

	//商家信息
	this.seller = null;

	//默认数据
	this.deliveryId   = 0;

	//免运费的商家ID
	this.freeFreight  = [];

	//订单各项数据
	this.orderAmount  = 0;//订单金额
	this.goodsSum     = 0;//商品金额
	this.deliveryPrice= 0;//运费金额
	this.paymentPrice = 0;//支付金额
	this.taxPrice     = 0;//税金
	this.protectPrice = 0;//保价
	this.ticketPrice  = 0;//代金券

	/**
	 * 算账
	 */
	this.doAccount = function()
	{
		//税金
		this.taxPrice = $('input:checkbox[name="taxes"]:checked').length > 0 ? $('input:checkbox[name="taxes"]:checked').val() : 0;
		//最终金额
		this.orderAmount = parseFloat(this.goodsSum) - parseFloat(this.ticketPrice) + parseFloat(this.deliveryPrice) + parseFloat(this.paymentPrice) + parseFloat(this.taxPrice) + parseFloat(this.protectPrice);

		this.orderAmount = this.orderAmount <=0 ? 0 : this.orderAmount.toFixed(2);

		//刷新DOM数据
		$('#final_sum').html(this.orderAmount);
		$('[name="ticket_value"]').html(this.ticketPrice);
		$('#delivery_fee_show').html(this.deliveryPrice);
		$('#protect_price_value').html(this.protectPrice);
		$('#payment_value').html(this.paymentPrice);
		$('#tax_fee').html(this.taxPrice);
	}

	
	/**
	 * delivery初始化
	 */
	this.deliveryInit = function(defaultDeliveryId)
	{
		this.deliveryId = defaultDeliveryId;
	}

	/**
	 * delivery选中
	 * @param int deliveryId 配送方式ID
	 */
	this.deliverySelected = function(deliveryId)
	{
		var deliveryObj = $('input[type="radio"][name="delivery_id"][value="'+deliveryId+'"]');
		this.protectPrice  = deliveryObj.data("protectPrice") > 0 ? deliveryObj.data("protectPrice") : 0;
		this.deliveryPrice = deliveryObj.data("deliveryPrice")> 0 ? deliveryObj.data("deliveryPrice"): 0;

		//先发货后付款
		if(deliveryObj.attr('paytype') == '1')
		{
			$('input[type="radio"][name="payment"]').prop('checked',false);
			$('input[type="radio"][name="payment"]').prop('disabled',true);
			$('#paymentBox').hide("slow");

			//支付手续费清空
			this.paymentPrice = 0;
		}
		else
		{
			$('input[type="radio"][name="payment"]').prop('disabled',false);
			$('#paymentBox').show("slow");
		}
		_self.doAccount();
	}

	/**
	 * payment初始化
	 */
	this.paymentInit = function(defaultPaymentId)
	{
		if(defaultPaymentId > 0)
		{
			$('input:radio[name="payment"][value="'+defaultPaymentId+'"]').trigger('click');
		}
	}

	/**
	 * payment选择
	 */
	this.paymentSelected = function(paymentId)
	{
		var paymentObj = $('input[type="radio"][name="payment"][value="'+paymentId+'"]');
		this.paymentPrice = paymentObj.attr("alt");
		this.doAccount();
	}

	/**
	 * 代金券显示
	 */
	this.ticketShow = function()
	{
		var sellerArray = [];
		for(var seller_id in this.seller)
		{
			sellerArray.push(seller_id);
		}

		art.dialog.open(creatUrl("block/ticket/sellerString/"+sellerArray.join("_")),{
			title:'选择代金券',
			okVal:'使用',
			ok:function(iframeWin, topWin)
			{
				//动态创建代金券节点
				_self.getForm().find("input[name='ticket_id[]']").remove();

				var formObject   = iframeWin.document.forms["ticketForm"];
				var resultTicket = 0;
				$(formObject).find("[name='ticket_id']:checked").each(function()
				{
					var sid    = $(this).attr('seller');
					var tprice = parseFloat($(this).attr('price'));

					//专用代金券
					if(_self.seller[sid] > 0)
					{
						resultTicket += (tprice >= _self.seller[sid]) ? _self.seller[sid] : tprice;
					}
					//通用代金券
					else if(sid == '0')
					{
						var maxPrice = 0;
						for(var sellerId in _self.seller)
						{
							if(_self.seller[sellerId] > maxPrice)
							{
								maxPrice = _self.seller[sellerId];
							}
						}
						resultTicket += (tprice >= maxPrice) ? maxPrice : tprice;
					}
					//动态插入节点
					_self.getForm().prepend("<input type='hidden' name='ticket_id[]' value='"+$(this).val()+"' />");
				});
				_self.ticketPrice = resultTicket;
				_self.doAccount();
			},
			"cancel":true,
			"cancelVal":"取消",
		});
	}

	//获取form表单
	this.getForm = function()
	{
		return $('form[name="order_form"]').length == 1 ? $('form[name="order_form"]') : $('form:first');
	}
}