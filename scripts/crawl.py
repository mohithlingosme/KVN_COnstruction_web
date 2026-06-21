import urllib.request
import urllib.parse
from html.parser import HTMLParser
import sys

base_url = "http://localhost/kvn_construction/public/"
visited = set()
broken = set()
to_visit = {base_url}

class LinkParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.links = []

    def handle_starttag(self, tag, attrs):
        if tag in ('a', 'link', 'script', 'img', 'form'):
            for attr, value in attrs:
                if attr in ('href', 'src', 'action'):
                    if value and not value.startswith(('javascript:', 'mailto:', 'tel:', '#')):
                        self.links.append(value)

while to_visit:
    url = to_visit.pop()
    if url in visited:
        continue
    visited.add(url)
    
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req) as response:
            if response.status == 200:
                if 'text/html' in response.headers.get('Content-Type', ''):
                    html = response.read().decode('utf-8')
                    parser = LinkParser()
                    parser.feed(html)
                    for link in parser.links:
                        full_url = urllib.parse.urljoin(url, link)
                        # only crawl same domain and path
                        if full_url.startswith(base_url):
                            if full_url not in visited and full_url not in broken:
                                to_visit.add(full_url)
    except urllib.error.HTTPError as e:
        if e.code == 404:
            broken.add(url)
            print(f"404: {url}")
        else:
            print(f"Error {e.code}: {url}")
    except Exception as e:
        print(f"Failed {url}: {e}")

print("Done. Found broken links:", len(broken))
for b in broken:
    print(b)
