# Backgrounds
Implementing GoogleTranslate can be done in two ways: In your frontend or in your backend. When you implement the Google Translation API in your frontend - by using a JavaScript widget provided by Google - the usage of their API is free. However, it has various downsides: The JavaScript widget will slow down your site. Even worse, when the Google API is down, your frontend will suffer as well. Also, the Magento content you offer will still be indexed only in the original language - there is no SEO benefit in using additional languages. Last but not least, whenever the API makes a mistake, whenever the API translates a phrase or sentence in a wrong way, there is no way for you to fix that mistake.

Our extension solves all of this. It allows you to translate in your backend, and then save that content to the Magento database. The frontend will simply load content in the usual way, with no additional JavaScript required. Also, because the content is translated manually (with a single click on the button), you can correct any translations afterwards. This makes our translation tool a must have when dealing with larger multilingual sites.

# GoogleTranslate or BingTranslate?
Both Google and Microsoft offer computer-based translation APIs. To make the right choice, you need to weigh in a couple of factors. GoogleTranslate charges for every translation you make, while the Microsoft Translation API (formerly called BingTranslate) is free up to a certain limit. Both offer a finejob when translating content, but some users claim that the Google translation is slightly better than the one of Microsoft. To test this, it is best to try out their free web-based version with your own text to compare the differences. Some useful links:

- Google Translate free widget: https://translate.google.com/ 
- Bing Translator free widget: http://www.bing.com/translator/
- Google Translate pricing pricing: https://developers.google.com/translate/v2/pricing
- Microsoft Translator pricing: http://datamarket.azure.com/dataset/bing/microsofttranslator 

If you purchase the GoogleTranslate extension from our site, you will get a 50% discount on the BingTranslate extension and vice versa. This allows you to easily cross-over when needed.
