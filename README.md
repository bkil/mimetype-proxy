# mimetype-proxy

A simple proof of concept proxy that changes the returned mime-type

## TODO

Pull requests welcome!

### High

* Security review
* Do we need @ signs anywhere?
* Pass through the IP address of the client in `X-Forwarded-For`

### Medium

* Only pass through content from the target which has `Content-Type` matching an allow list
* Queue requests so that the upstream server is not overloaded in bursts or at least do some locking for a similar effect (push queuing down to the client)
* Rate limit based on client IP

### Low

* Compute `Date`,  `Last-Modified`, `Expires` header
* Produce a main index page that shows various statistics (failure rate, request rate, bandwidth usage, user count, heatmap in case of tile servers)

### Wishlist

* If the target server returns or `ETag`, pass it through, otherwise synthesize a value
* Pass through `ETag`, `If-Modified-Since` and `If-None-Match` from the client request
* Check return value of `fpassthru()`
* If the target returns longer `Cache-Control` values than our default, return that one
* It would be awesome to pipeline requests towards each server through IPC with longer running processes in the background, but only a foreground worker based kludge would work on shared hosting
* Either pass through the `User-Agent` or set an explicit one (containing a link to the repository)
* Set `Referer` to the repository
* Prioritize downloading of the highest zoom level first in case of standing in queue
* Restrict client by country based on GeoIP
* Start buffering of content and return mime type based on magic
* If the returned `Content-Length` or the first 1kB of magic indicates that a .png is returned instead of a .jpeg and the pipeline isn't too deep, open another socket to request the remaining files to avoid head of line blocking.

## LICENSE

* AGPLv3
  * https://www.gnu.org/licenses/agpl-3.0.en.html
