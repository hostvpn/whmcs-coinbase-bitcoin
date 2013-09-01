Coinbase (Bitcoin) Payment Gateway Module for WHMCS
======================

A payment gateway module for WHMCS and Coinbase's bitcoin payment API.


How To Install
======================
- Move the contents of this module into the root WHMCS installation
  directory.
- Login to the admin side of WHMCS, and go to Setup -> Payments ->
  Payment Gateways.
- Select this module ("Coinbase (Bitcoin)") from the drop-down select box, and then click the Activate button to the right of it.
- On this same page, you can now configure the module, mainly you'll want to pay close attention to these items:
  - **Coinbas API Key** - This is generated from within Coinbase, keep this private
  - **Coinbase CA Cert Path** - This will be the path where the ca-coinbase.crt is.
  - **Coinbase Callback Secret** - Generate a long and random string and input it in this field. Provide this to Coinbase on the backend. This is used to authenticate their callback.
- Save your changes when you're done configuring.  It's probably a smart idea to do a test transaction.

To Do
======================

- [x] Verify SSL connection to Coinbase API with cURL.
- [x] WHMCS 'admin' username is hardcoded in callbacks/coinbase.php (line 15). Make that less brittle/more flexible.
- [x] Additional error checking in 'callbacks/coinbase.php'
- [] Refunds may not be possible; if someone in WHMCS tries to refund an
  invoice from Coinbase, need to provide a "friendly" message.
- [] Error checking needed on all of the variables initialized in the 'coinbase_config' function.
- [] ...



License
======================

This software is released under the [MIT License](http://opensource.org/licenses/MIT).
