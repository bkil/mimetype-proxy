# mimetype-proxy

A simple proof of concept proxy that changes the returned mime-type

## TODO

Pull requests welcome!

### High

* Security review
* Do we need @ signs anywhere?
* Sanitize `mime` and `url` parameters

### Medium

* Return more refined HTTP status codes on error
* Check whether connection timeout is set for the target server and set one shorter than our own timeout
* Queue requests so that the upstream server is not overloaded in bursts or at least do some locking for a similar effect (push queuing down to the client)

### Low

* If the target server returns `Content-length`, we may also pass that along
* If the target server returns `Date`, `Last-Modified` or `ETag`, pass it through, otherwise synthesize a value
* Pass through `ETag`, `If-Modified-Since` and `If-None-Match` from the client request
* Check return value of `fpassthru()`
* Only pass through content from the target which has `Content-type` matching an allow list

### Wishlist

* If the target returns longer `Cache-Control` values than our default, return that one
* Expires header?
* It would be awesome to pipeline requests towards each server through IPC with longer running processes in the background, but only a foreground worker based kludge would work on shared hosting
* Either pass through the `User-Agent` or set an explicit one

## LICENSE

* AGPLv3
  * https://www.gnu.org/licenses/agpl-3.0.en.html
