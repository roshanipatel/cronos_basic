
function AjaxImageLoader( props )
{
    if( ! props.id )
    {
        alert( "Missing required id" );
        return;
    }
    
    this._normalizeId = function( id )
    {
        if( ( id.length == 0 ) || ( id.charAt( 0 ) == "#" ) )
            return id;
        else
            return "#" + id;
    };
    
    this.create = function()
    {
        // Create image inside wrapper
        jQuery( this.id )
            .css( 'text-align', 'center' )
            .html("<img src='" + props.source + "'/>");
        this.hide();
    };
    this.show = function()
    {
        jQuery(this.id).css( "display", this.display );
    };
    this.hide = function()
    {
        jQuery(this.id).css( "display", "none" );
    };
    
    this.id = this._normalizeId( props.id );
    if( props.display )
        this.display = props.display;
    else
        this.display = "inline";
    this.create();
}
