/**
 * Created by GiantX on 2017/5/13.
 */
JsBarcode("#barcode", "Hi!");
// or with jQuery
$("#barcode").JsBarcode("Hi!");

JsBarcode("#barcode", "1234", {
  format: "pharmacode",
  lineColor: "#0aa",
  width:4,
  height:40,
  displayValue: false
});

JsBarcode("#barcode")
  .options({font: "OCR-B"}) // Will affect all barcodes
  .EAN13("1234567890128", {fontSize: 18, textMargin: 0})
  .blank(20) // Create space between the barcodes
  .EAN5("12345", {height: 85, textPosition: "top", fontSize: 16, marginTop: 15})
  .render();


/* Use any jsbarcode-* or data-* as attributes where * is any option. */
/* <svg class="barcode"
 jsbarcode-format="upc"
 jsbarcode-value="123456789012"
 jsbarcode-textmargin="0"
 jsbarcode-fontoptions="bold">
 </svg>
 */
JsBarcode(".barcode").init();