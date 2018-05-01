[{$smarty.block.parent}]

[{block name="oegdprbase_delete_shipping_address"}]
    [{if $oxcmp_user }]
        [{foreach from=$oxcmp_user->getUserAddresses() item=address name="shippingAdresses"}]
            <span id="oegdprbase-delete-shipping-address-button-[{$address->oxaddress__oxid->value}]" class="btn btn-danger btn-xs  hasTooltip pull-right dd-action oegdprbase-delete-shipping-address-button "
                    title="[{oxmultilang ident="OEGDPRBASE_DELETE"}]"
                    style="margin-top: 5px;"
                    data-toggle="modal"
                    data-target="#delete_shipping_address_[{$address->oxaddress__oxid->value}]">
                <i class="fa fa-trash">[{oxmultilang ident="OEGDPRBASE_DELETE"}]</i>
            </span>
        [{/foreach}]
    [{/if}]
    [{oxscript add='
    var selectAddressDropDown = $("#addressId");
    var activeAddressId = selectAddressDropDown.val();
    var deleteButton = $("#oegdprbase-delete-shipping-address-button-"+activeAddressId);
    if ($("#addressId").val() != "-1") {
        deleteButton.show();
    }
    selectAddressDropDown.on("change", function() {
      if (this.value == "-1") {
        deleteButton.hide();
      } else {
        deleteButton.show();
      }
    })
'}]
[{/block}]