Wibble Proposal: HTML/XML Sanitisation And Manipulation For Zend Framework
==========================================================================

Note: Wibble is its super-not-so-secret codename ;).

Description
-----------

Wibble will be a filter based HTML/XML sanitizer and manipulator which traverses all nodes of a source DOM to apply arbitrary filtering rules. Wibble can selectively update source HTML or XML and export a well-formed document or fragment containing the results of its filtering.

The uses for Wibble vary, however it's primarily targeted at applying changes to nodes of a document's DOM based on some nodal condition (e.g. a tagname or xpath expression or attribute value). This is useful, for example, in stripping source markup of illegal tags and attributes or replacing/modifying specific nodes. The filter logic utilised can be of any complexity.

For example, you may determine that all attributes in an HTML document called "style" should be removed. Wibble allows you to define a filter to accomplish this, which can then traverse the source's DOM applying the filter to all nodes within the document's DOM. At each node, the filter could assess if that node is an element with the style attribute, and delete the attribute if present. (Note: Wibble would have a built in strip tags filter.)

The background of writing Wibble was the problem of sanitizing untrusted HTML so it is safe for output to browsers (without substantial alteration or the application of a native PHP escape mechanism) in an efficient performant manner. A common use case for this are user comments on a blog where a subset of HTML is allowable. Often, this leads to the adoption of markup languages like BBCode or Markdown instead of worrying about HTML trust and safety. The domain of sanitizing HTML is not, however, always that simple to avoid. RSS or Atom feeds also carry HTML content intended for output, and the source of such feeds (like any input) can be untrustworthy, i.e. we must filter/validate the input markup prior to output. The fallback to an alternative markup language is not possible in this case, and so, a HTML sanitizer/manipulator comes in handy. Also, markup language fallbacks are themselves subject to security issues if not properly written/maintained.

Suitability For Zend Framework
------------------------------

The Zend Framework is noticeably missing a HTML sanitiser, or any form of simple HTML manipulation tool for that matter. HTML and XML as forms of user/third-party input are not comprehensively addressed by the framework which leaves a large gap in its security architecture.

The sole HTML sanitisation component existing in the framework is Zend_Filter_StripTags. In late 2009, this component was revealed to have several security vulnerabilities due to design flaws and/or regular expression errors. The component, while now fixed, is far from suitable for its apparently intended purpose. This highlights that users (and developers) are confused about sanitising HTML and that a complete solution addressing all concerns is required.

Part of this proposal would be the recommendation that Zend_Filter_StripTags is deprecated/removed for ZF 2.0.

HTML Sanitisation Solutions
---------------------------

The primary PHP solution for sanitising HTML is the HTMLPurifier library. HTMLPurifier is a large, complex, and relatively slow library which offers excellent HTML sanitisation. Most other solutions are based on regular expression parsing which lends them speed at the expense of malformed output and being under constant threat that their parsing logic will be bypassed.

All existing used solutions tend to share one or more of the following user complaints.

1. Reliance on regular expressions for parsing

Regular expressions are historically unreliable when it comes to something like parsing/altering HTML since the standards are so loose that parsing must deal with numerous yet valid HTML markup oddities not to mention numerous standards. Also, because the wellformedness of the resulting document is not subject to a comprehensive validation/rendering approach, they often result in malformed or non-standard HTML output leading to unpredictable results when rendered. Sanitizers of this type have a long standing history of containing security exploits or needing consistent updating to reflect new exploits (see Zend_Filter_StripTags).

2. Reliance on comprehensive but slow parsing

In place of regular expressions, such solutions use a token based parser which is far more comprehensive than regular expressions (by orders of magnitude) but can be slow and cumbersome. Usually, however, the performance hit is worth it in exchange for wellformed sanitized output. Thus the issue in such cases is typically performance, size and the resulting restriction of uses to least-costly times (e.g. on input when results can be cached). Sanitizers of this type tend to be the most secure and broadly compatible options. However, their performance has always discouraged use where avoidable.

3. Lack of maintainance

Many (almost all) HTML sanitizers in PHP have fallen into unmaintained states and an unmaintained security library is a dangerous thing. The number of actively maintained sanitizers is extremely small. The source of much of the needed maintenance is derived from the use of complex parsers, regular expressions, blacklists (require constant updating/review), and other general requirements. Without regular maintenance, such sanitization solutions may become insecure over time.

4. Lack of wellformed output

Most simplified sanitizers (primarily those driven by regular expressions) treat HTML as a string rather than as a structured HTML document. The result is often that output from such sanitization processes is no longer well formed (i.e. adherent to a HTML standard). This can have curious side-effects in output depending on the browser. For example, what happens if a sanitized </div> tag (it contains no XSS and has no matching opening tag) gets into the middle of your otherwise valid XHTML 1.0 Strict page (assuming such a tag was allowable)? XSS is not the only reason to sanitize input HTML. The capability to deface or break sites is still a prevelant risk that needs addressing.

5. Incomplete/Misleading/Misdirected

Sanitizing HTML is one of the more obscure aspects of preventing Cross-Site Scripting (XSS) and as a result there are a wide variety of approaches from the naive (run it through Tidy!) to the incomplete (PHP's strip_tags()) to the profoundly unhappy (dodgy BBCode parsers). It's little wonder programmers get confused. When you decide to parse Markdown from comments instead of HTML and find out that your Markdown parser can generate XSS riddled HTML, you're in this category. It must be noted that sanitizing HTML is a multi-step procedure and omitting any of the necessary steps is a Bad Thing (TM).

Theory Of Operation
-------------------

Wibble is designed to operate under a set of compromises that balance speed with sanitisation capability and reduced maintenance needs. To achieve this, Wibble depends on PHP DOM and the HTML Tidy extension, along with a minimal set of regular expressions. Given that Tidy is an optional extension, but highly recommended, it has been assumed that users may elect to opt-out of using it at their own risk. Omitting Tidy will not lead to XSS exploits, but it is Wibble's primary defence against malformed output. The opt-out nature is explicit, not silently applied - not opting out would result in an Exception.

Wibble operates using two sets of classes: Wibble\HTML and Wibble\Filter. Wibble\HTML contains two classes: Document and Fragment. The difference between both is limited to their resulting output. Document outputs a whole HTML/XML document while Fragment only outputs the contents of the root or body element. Both parse and work on markup identically otherwise.

Each new Document/Fragment defines a filter() method accepting one of:

1. Internal named filter
2. Object of type Wibble\Filter\Filterable
3. Closure accepting a \DOMNode as a parameter

Once called, filter() traverses the entire DOM and calls the provided filter on all nodes encountered. This is a recursive process, so any one filter procedure can terminate itself and pass control back to the parent procedure (handy if a filter should skip processing an entire child tree).

The filters themselves are easy to write and understand - they are written to manipulate the DOM so there is no new concept that needs to be learned. Here's a simple example where we may create a filter to selectively apply a new style to all div tags in a document (we like red text for some reason):

    use Wibble;
    $doc = new Wibble\HTML\Document($markup);
    $doc->filter(function(\DOMNode){
        if ($node->nodeType !== XML_ELEMENT_NODE || $node->tagName !== 'div') {
            return;
        }
        $currentStyle = '';
        if ($node->hasAttribute('style')) {
            $currentStyle = $node->getAttribute('style');
        }
        $node->setAttribute('style', 'color: red;' . $currentStyle);
    });

Two proposed internal filters will be: Escape and StripTags. The former filters HTML and automatically escapes all tags and attributes not present on Wibble's internal whitelist. The latter accepts a user defined list of allowed elements and attributes (any others are escaped/stripped).

If the proposal is accepted, it is expected that several default filters from Wibble can be encapsulated into View Helpers to streamline simple use cases.

As the above outline presents, the component would be relatively simple. There are no token based parsers that need heavy development or maintainance input, the use of C extensions over native parsers should provide some speed, and the DOM/Tidy combo stand in for any unwarranted dependence on regular expressions.
