Coinbase (Bitcoin) Payment Gateway Module for WHMCS
======================

A payment gateway module for WHMCS and Coinbase's bitcoin payment API.



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
