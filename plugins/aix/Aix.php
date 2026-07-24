<?php

namespace Altum\Plugin;

use Altum\Plugin;

class Aix {
    public static $plugin_id = 'aix';

    public static function install() {
        $user_id_type = in_array(PRODUCT_KEY, ['seamlessqrcode', '66biolinks']) ? 'int' : 'bigint unsigned';

        /* Run the installation process of the plugin */
        $queries = [
            "INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('aix', '{\"openai_api_key\":\"\",\"documents_is_enabled\":true,\"images_is_enabled\":true,\"transcriptions_is_enabled\":true,\"images_display_latest_on_index\":true,\"input_moderation_is_enabled\":null,\"documents_available_languages\":[\"English\",\"Chinese\",\"Hindi\",\"Spanish\",\"French\",\"Arabic\",\"Bengali\",\"Russian\",\"Portuguese\",\"Indonesian\",\"Urdu\",\"German\",\"Japanese\",\"Punjabi\",\"Javanese\",\"Telugu\",\"Turkish\",\"Marathi\",\"Hungarian\",\"Romanian\",\"Italian\",\"Ukrainian\",\"Polish\",\"Greek\",\"Swedish\",\"Czech\",\"Serbian\",\"Bulgarian\",\"Croatian\",\"Hebrew\"],\"images_available_artists\":[\"Leonardo da Vinci\",\"Vincent van Gogh\",\"Pablo Picasso\",\"Salvador Dali\",\"Banksy\",\"Takashi Murakami\",\"George Condo\",\"Tim Burton\",\"Normal Rockwell\",\"Andy Warhol\",\"Claude Monet\"],\"chats_is_enabled\":\"on\",\"chats_assistant_name\":\"66aix bot\",\"chats_avatar\":\"\",\"access_key\":\"\",\"secret_access_key\":\"\",\"region\":\"eu-central-1\",\"syntheses_is_enabled\":true}');",
            "alter table users add aix_documents_current_month bigint unsigned default 0 after source;",
            "alter table users add aix_words_current_month bigint unsigned default 0 after source;",
            "alter table users add aix_images_current_month bigint unsigned default 0 after source;",
            "alter table users add aix_transcriptions_current_month bigint unsigned default 0 after source;",
            "alter table users add aix_chats_current_month bigint unsigned default 0 after source;",
            "alter table users add aix_syntheses_current_month bigint unsigned default 0 after source;",
            "alter table users add aix_synthesized_characters_current_month bigint unsigned default 0 after source;",

            "CREATE TABLE `templates_categories` (
            `template_category_id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(128) DEFAULT NULL,
            `settings` text,
            `icon` varchar(32) DEFAULT NULL,
            `emoji` varchar(32) DEFAULT NULL,
            `color` varchar(16) DEFAULT NULL,
            `background` varchar(16) DEFAULT NULL,
            `order` int DEFAULT NULL,
            `is_enabled` tinyint unsigned DEFAULT '1',
            `datetime` datetime DEFAULT NULL,
            `last_datetime` datetime DEFAULT NULL,
            PRIMARY KEY (`template_category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "INSERT INTO `templates_categories` (`template_category_id`, `name`, `settings`, `icon`, `emoji`, `color`, `background`, `order`, `is_enabled`, `datetime`, `last_datetime`) VALUES
            (1, 'Text', '{\"translations\":{\"english\":{\"name\":\"Text\"}}}', 'fas fa-paragraph', '📝', '#14b8a6', '#f0fdfa', 1, 1, '2023-03-25 17:33:19', NULL),
            (2, 'Website', '{\"translations\":{\"english\":{\"name\":\"Website\"}}}', 'fas fa-globe', '🌐', '#0ea5e9', '#f0f9ff', 1, 1, '2023-03-25 17:33:19', NULL),
            (3, 'Social Media', '{\"translations\":{\"english\":{\"name\":\"Social Media\"}}}', 'fas fa-hashtag', '🕊️', '#3b82f6', '#eff6ff', 1, 1, '2023-03-25 17:33:19', NULL),
            (4, 'Others', '{\"translations\":{\"english\":{\"name\":\"Others\"}}}', 'fas fa-fire', '🔥', '#6366f1', '#eef2ff', 1, 1, '2023-03-25 17:33:19', NULL),
            (5, 'Developers', '{\"translations\":{\"english\":{\"name\":\"Developers\"}}}', 'fas fa-code', '💻', '#DB00FF', '#FCE9FF', 1, 1, '2023-04-19 20:00:55', NULL);",

            "CREATE TABLE `templates` (
            `template_id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `template_category_id` bigint unsigned DEFAULT NULL,
            `name` varchar(128) DEFAULT NULL,
            `prompt` text,
            `settings` text,
            `icon` varchar(32) DEFAULT NULL,
            `order` int DEFAULT NULL,
            `total_usage` bigint unsigned DEFAULT '0',
            `is_enabled` tinyint unsigned DEFAULT '1',
            `datetime` datetime DEFAULT NULL,
            `last_datetime` datetime DEFAULT NULL,
            PRIMARY KEY (`template_id`),
            KEY `template_category_id` (`template_category_id`),
            CONSTRAINT `templates_ibfk_1` FOREIGN KEY (`template_category_id`) REFERENCES `templates_categories` (`template_category_id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "INSERT INTO `templates` (`template_category_id`, `name`, `prompt`, `settings`, `icon`, `order`, `total_usage`, `is_enabled`, `datetime`, `last_datetime`) VALUES
            (1, 'Summarize', 'Summarize the following text: {text}', '{\"translations\":{\"english\":{\"name\":\"Summarize\",\"description\":\"Get a quick summary of a long piece of text, only the important parts.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text to summarize\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-align-left', 1, 1, 1, '2023-03-25 23:28:59', NULL),
            (1, 'Explain like I am 5', 'Explain & summarize the following text like I am 5: {text}', '{\"translations\":{\"english\":{\"name\":\"Explain like I am 5\",\"description\":\"Get a better understanding on a topic, subject or piece of text.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text or concept to explain\",\"placeholder\":\"How does a rocket go into space?\",\"help\":null}}}}}', 'fas fa-child', 2, 1, 1, '2023-03-25 23:28:59', NULL),
            (1, 'Text spinner/rewriter', 'Rewrite the following text in a different manner: {text}', '{\"translations\":{\"english\":{\"name\":\"Text spinner/rewriter\",\"description\":\"Rewrite a piece of text in another unique way, using different words.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text to rewrite\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-sync', 3, 1, 1, '2023-03-25 23:28:59', NULL),
            (1, 'Keywords generator', 'Extract important keywords from the following text: {text}', '{\"translations\":{\"english\":{\"name\":\"Keywords generator\",\"description\":\"Extract important keywords from a piece of text.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text to extract keywords from\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-key', 4, 1, 1, '2023-03-25 23:28:59', NULL),
            (1, 'Grammar fixer', 'Fix the grammar on the following: {text}', '{\"translations\":{\"english\":{\"name\":\"Grammar fixer\",\"description\":\"Make sure your text is written correctly with no spelling or grammar errors.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text to grammar fix\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-spell-check', 5, 1, 1, '2023-03-25 23:28:59', NULL),
            (1, 'Text to Emoji', 'Transform the following text into emojis: {text}', '{\"translations\":{\"english\":{\"name\":\"Text to Emoji\",\"description\":\"Convert the meaning of a piece of text to fun emojis.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text to convert\",\"placeholder\":\"The pirates of the Caribbean\",\"help\":null}}}}}', 'fas fa-smile-wink', 6, 1, 1, '2023-03-25 23:28:59', NULL),
            (1, 'Blog Article Idea', 'Write multiple blog article ideas based on the following: {text}', '{\"translations\":{\"english\":{\"name\":\"Blog Article Idea\",\"description\":\"Generate interesting blog article ideas based on the topics that you want.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Title or Keywords\",\"placeholder\":\"Best places to travel as a couple\",\"help\":null}}}}}', 'fas fa-lightbulb', 7, 1, 1, '2023-03-25 23:29:00', NULL),
            (1, 'Blog Article Intro', 'Write a good intro for a blog article, based on the title of the blog post: {text}', '{\"translations\":{\"english\":{\"name\":\"Blog Article Intro\",\"description\":\"Generate a creative intro section for your blog article.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-heading\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Title of the blog article\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-keyboard', 8, 1, 1, '2023-03-25 23:29:00', NULL),
            (1, 'Blog Article Idea & Outline', 'Write ideas for a blog article title and outline, based on the following: {text}', '{\"translations\":{\"english\":{\"name\":\"Blog Article Idea & Outline\",\"description\":\"Generate unlimited blog article ideas and structure with ease.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Title or Keywords\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-blog', 9, 1, 1, '2023-03-25 23:29:00', NULL),
            (1, 'Blog Article Section', 'Write a blog sections about \"{title}\" using the \"{keywords}\" keywords', '{\"translations\":{\"english\":{\"name\":\"Blog Article Section\",\"description\":\"Generate a full and unique section/paragraph for your blog article.\"}},\"inputs\":{\"title\":{\"icon\":\"fas fa-heading\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Title\",\"placeholder\":\"Best traveling tips and tricks\",\"help\":null}}},\"keywords\":{\"icon\":\"fas fa-file-word\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Keywords\",\"placeholder\":\"Airport luggage, Car rentals, Quality Airbnb stays\",\"help\":null}}}}}', 'fas fa-rss', 10, 1, 1, '2023-03-25 23:29:00', NULL),
            (1, 'Blog Article', 'Write a long article / blog post on \"{title}\" with the \"{keywords}\" keywords and the following sections \"{sections}\"', '{\"translations\":{\"english\":{\"name\":\"Blog Article\",\"description\":\"Generate a simple and creative article / blog post for your website.\"}},\"inputs\":{\"title\":{\"icon\":\"fas fa-heading\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Title\",\"placeholder\":\"Places you must visit in winter\",\"help\":null}}},\"keywords\":{\"icon\":\"fas fa-file-word\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Keywords\",\"placeholder\":\"Winter, Hotel, Jacuzzi, Spa, Ski\",\"help\":null}}},\"sections\":{\"icon\":\"fas fa-feather\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Sections\",\"placeholder\":\"Austria, Italy, Switzerland\",\"help\":null}}}}}', 'fas fa-feather', 11, 1, 1, '2023-03-25 23:29:00', NULL),
            (1, 'Blog Article Outro', 'Write a blog article outro based on the blog title \"{title}\" and the \"{description}\" description', '{\"translations\":{\"english\":{\"name\":\"Blog Article Outro\",\"description\":\"Generate the conclusion section of your blog article.\"}},\"inputs\":{\"title\":{\"icon\":\"fas fa-heading\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Title\",\"placeholder\":\"Warm places to visit in December\",\"help\":null}}},\"description\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Description\",\"placeholder\":\"Describe what your blog article is about\",\"help\":null}}}}}', 'fas fa-pen-nib', 12, 1, 1, '2023-03-25 23:29:00', NULL),
            (1, 'Reviews', 'Write a review or testimonial about \"{name}\" using the following description: {description}', '{\"translations\":{\"english\":{\"name\":\"Reviews\",\"description\":\"Generate creative reviews / testimonials for your service or product.\"}},\"inputs\":{\"name\":{\"icon\":\"fas fa-heading\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Product or service name\",\"placeholder\":\"Wandering Agency: Travel with confidence\",\"help\":null}}},\"description\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Description\",\"placeholder\":\"We plan and set up your perfect traveling experience to the most exotic places, from start to finish.\",\"help\":null}}}}}', 'fas fa-star', 13, 1, 1, '2023-03-25 23:29:00', NULL),
            (1, 'Translate', 'Translate the following text: {text}', '{\"translations\":{\"english\":{\"name\":\"Translate\",\"description\":\"Translate a piece of text to another language with ease.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-language', 14, 1, 1, '2023-03-25 23:29:00', NULL),
            (3, 'Social media bio', 'Write a short social media bio profile description based on those keywords: {text}', '{\"translations\":{\"english\":{\"name\":\"Social media bio\",\"description\":\"Generate Twitter, Instagram, TikTok bio for your account.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-file-word\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Keywords to be used\",\"placeholder\":\"Yacht traveling, Boat charter, Summer, Sailing\",\"help\":null}}}}}', 'fas fa-share-alt', 15, 1, 1, '2023-03-25 23:29:00', NULL),
            (3, 'Social media hashtags', 'Generate hashtags for a social media post based on the following description: {text}', '{\"translations\":{\"english\":{\"name\":\"Social media hashtags\",\"description\":\"Generate hashtags for your social media posts.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text to extract hashtags from\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-hashtag', 16, 1, 1, '2023-03-25 23:29:00', NULL),
            (3, 'Video Idea', 'Write ideas for a video scenario, based on the following: {text}', '{\"translations\":{\"english\":{\"name\":\"Video Idea\",\"description\":\"Generate a random video idea based on the topics that you want.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Title or Keywords\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-video', 17, 1, 1, '2023-03-25 23:29:00', NULL),
            (3, 'Video Title', 'Write a video title, based on the following: {text}', '{\"translations\":{\"english\":{\"name\":\"Video Title\",\"description\":\"Generate a catchy video title for your video.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Title or Keywords\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-play', 18, 1, 1, '2023-03-25 23:29:00', NULL),
            (3, 'Video Description', 'Write a video description, based on the following: {text}', '{\"translations\":{\"english\":{\"name\":\"Video Description\",\"description\":\"Generate a brief and quality video description.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Title or Keywords\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-film', 19, 1, 1, '2023-03-25 23:29:00', NULL),
            (3, 'Tweet generator', 'Generate a tweet based on the following text/keywords: {text}', '{\"translations\":{\"english\":{\"name\":\"Tweet generator\",\"description\":\"Generate tweets based on your ideas/topics/keywords.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text or keywords to be used\",\"placeholder\":null,\"help\":null}}}}}', 'fab fa-twitter', 20, 1, 1, '2023-03-25 23:29:00', NULL),
            (3, 'Instagram caption', 'Generate an instagram caption for a post based on the following text/keywords: {text}', '{\"translations\":{\"english\":{\"name\":\"Instagram caption\",\"description\":\"Generate an instagram post caption based on text or keywords.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text or keywords to be used\",\"placeholder\":null,\"help\":null}}}}}', 'fab fa-instagram', 21, 1, 1, '2023-03-25 23:29:00', NULL),
            (2, 'Website Headline', 'Write a website short headline for the \"{name}\" product with the following description: {description}', '{\"translations\":{\"english\":{\"name\":\"Website Headline\",\"description\":\"Generate creative, catchy and unique headlines for your website.\"}},\"inputs\":{\"name\":{\"icon\":\"fas fa-heading\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Product or service name\",\"placeholder\":\"Sunset Agents: Best summer destinations\",\"help\":null}}},\"description\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Description\",\"placeholder\":\"Our blog helps you find and plan your next summer vacation.\",\"help\":null}}}}}', 'fas fa-feather', 22, 1, 1, '2023-03-25 23:29:00', NULL),
            (2, 'SEO Title', 'Write an SEO Title for a web page based on those keywords: {text}', '{\"translations\":{\"english\":{\"name\":\"SEO Title\",\"description\":\"Generate high quality & SEO ready titles for your web pages.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-file-word\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Keywords to be used\",\"placeholder\":\"Traveling, Summer, Beach, Pool\",\"help\":null}}}}}', 'fas fa-heading', 23, 1, 1, '2023-03-25 23:29:00', NULL),
            (2, 'SEO Description', 'Write an SEO description, maximum 160 characters, for a web page based on the following: {text}', '{\"translations\":{\"english\":{\"name\":\"SEO Description\",\"description\":\"Generate proper descriptions for your web pages to help you rank better\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text or keywords to be used\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-pen', 24, 1, 1, '2023-03-25 23:29:00', NULL),
            (2, 'SEO Keywords', 'Write SEO meta keywords, maximum 160 characters, for a web page based on the following: {text}', '{\"translations\":{\"english\":{\"name\":\"SEO Keywords\",\"description\":\"Extract and generate meaningful and quality keywords for your website.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text to extract keywords from\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-file-word', 25, 1, 1, '2023-03-25 23:29:00', NULL),
            (2, 'Ad Title', 'Write a short ad title, based on the following: {text}', '{\"translations\":{\"english\":{\"name\":\"Ad Title\",\"description\":\"Generate a short & good title copy for any of your ads.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text or keywords to be used\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-money-check-alt', 26, 1, 1, '2023-03-25 23:29:00', NULL),
            (2, 'Ad Description', 'Write a short ad description, based on the following: {text}', '{\"translations\":{\"english\":{\"name\":\"Ad Description\",\"description\":\"Generate the description for an ad campaign.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-th-list', 27, 1, 1, '2023-03-25 23:29:00', NULL),
            (4, 'Name generator', 'Generate multiple & relevant product names based on the following text/keywords: {text}', '{\"translations\":{\"english\":{\"name\":\"Name generator\",\"description\":\"Generate interesting product names for your project.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text or keywords to be used\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-file-signature', 28, 1, 1, '2023-03-25 23:29:00', NULL),
            (4, 'Startup ideas', 'Generate multiple & relevant startup business ideas based on the following text/keywords: {text}', '{\"translations\":{\"english\":{\"name\":\"Startup ideas\",\"description\":\"Generate startup ideas based on your topic inputs.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text or keywords to be used\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-user-tie', 29, 1, 1, '2023-03-25 23:29:00', NULL),
            (4, 'Viral ideas', 'Generate a viral idea based on the following text/keywords: {text}', '{\"translations\":{\"english\":{\"name\":\"Viral ideas\",\"description\":\"Generate highly viral probability ideas based on your topics or keywords.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text or keywords to be used\",\"placeholder\":null,\"help\":null}}}}}', 'fas fa-bolt', 30, 1, 1, '2023-03-25 23:29:01', NULL),
            (4, 'Custom prompt', '{text}', '{\"translations\":{\"english\":{\"name\":\"Custom prompt\",\"description\":\"Ask our AI for anything & he will do it is best to give you quality content.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Question or task\",\"placeholder\":\"What are the top 5 most tourist friendly destinations?\",\"help\":null}}}}}', 'fas fa-star', 31, 1, 1, '2023-03-25 23:29:23', NULL),
            (5, 'PHP snippet', 'You are a PHP programmer, answer the following request with a PHP snippet:\r\n\r\n{text}', '{\"translations\":{\"english\":{\"name\":\"PHP snippet\",\"description\":\"Generate PHP code snippets with ease.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Requested code\",\"placeholder\":\"Code that connects to a MySQL database in procedural style\",\"help\":\"Ask the AI what PHP code you want to receive \\/ get help with.\"}}}}}', 'fab fa-php', 32, 1, 1, '2023-04-19 20:18:43', NULL),
            (5, 'SQL query', 'You are a SQL programmer, answer the following request with an SQL query:\r\n\r\n{text}', '{\"translations\":{\"english\":{\"name\":\"SQL query\",\"description\":\"Generate helpful SQL queries with the help of AI.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Requested query\",\"placeholder\":\"Code that calculates the average from 3 columns\",\"help\":\"Ask the AI what SQL query you want to receive \\/ get help with.\"}}}}}', 'fas fa-database', 33, 1, 1, '2023-04-19 21:06:04', '2023-04-19 21:10:50'),
            (5, 'JS snippet', 'You are a JS programmer, answer the following request with a JS snippet:\r\n\r\n{text}', '{\"translations\":{\"english\":{\"name\":\"JS snippet\",\"description\":\"Generate quick & helpful Javascript code snippets.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Requested code\",\"placeholder\":\"Code that helps trigger and catch custom events\",\"help\":\"Ask the AI what JS code you want to receive \\/ get help with.\"}}}}}', 'fab fa-js', 34, 0, 1, '2023-04-19 21:31:37', NULL),
            (5, 'HTML snippet', 'You are a HTML programmer, answer the following request with a HTML snippet:\r\n\r\n{text}', '{\"translations\":{\"english\":{\"name\":\"HTML snippet\",\"description\":\"Generate simple and fast HTML pieces of code.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Requested code\",\"placeholder\":\"Code that generates a blank HTML page\",\"help\":\"Ask the AI what HTML code you want to receive \\/ get help with.\"}}}}}', 'fab fa-html5', 35, 0, 1, '2023-04-19 22:00:58', NULL),
            (5, 'CSS snippet', 'You are a CSS programmer, answer the following request with a CSS snippet:\r\n\r\n{text}', '{\"translations\":{\"english\":{\"name\":\"CSS snippet\",\"description\":\"Generate CSS classes & code snippets with ease.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Requested code\",\"placeholder\":\"Code that generates a gradient background class\",\"help\":\"Ask the AI what CSS code you want to receive \\/ get help with.\"}}}}}', 'fab fa-css3', 36, 0, 1, '2023-04-19 22:03:16', NULL),
            (5, 'Python snippet', 'You are a python programmer, answer the following request with a python snippet:\r\n\r\n{text}', '{\"translations\":{\"english\":{\"name\":\"Python snippet\",\"description\":\"Generate Python code pieces with the help of AI.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Requested code\",\"placeholder\":\"Code that sends an external HTTP request\",\"help\":\"Ask the AI what Python code you want to receive \\/ get help with.\"}}}}}', 'fab fa-python', 37, 0, 1, '2023-04-19 22:05:03', NULL),
            (1, 'Quote generator', 'Generate a random quote on the following topic: {topic}', '{\"translations\":{\"english\":{\"name\":\"Quote generator\",\"description\":\"Get random quotes based on the topic you wish.\"}},\"inputs\":{\"topic\":{\"icon\":\"fas fa-pen\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Topic\",\"placeholder\":\"Motivational\",\"help\":\"Input the type of quote you wish to generate.\"}}}}}', 'fas fa-bolt', 1, 1, 1, '2023-03-28 20:32:15', '2023-05-13 21:08:06'),
            (3, 'LinkedIn post', 'Generate a LinkedIn post based on the following text/keywords: {text}', '{\"translations\":{\"english\":{\"name\":\"LinkedIn post\",\"description\":\"Generate a great LinkedIn post based on text or keywords.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text or keywords to be used\",\"placeholder\":\"\",\"help\":\"\"}}}}}', 'fab fa-linkedin', 22, 0, 1, '2023-05-13 19:41:14', NULL),
            (3, 'Twitter thread generator', 'Generate a full Twitter thread based on the following text/keywords: {text}', '{\"translations\":{\"english\":{\"name\":\"Twitter thread generator\",\"description\":\"Generate a full thread based on any topic or idea.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text or keywords to be used\",\"placeholder\":\"\",\"help\":\"\"}}}}}', 'fab fa-twitter', 23, 0, 1, '2023-05-13 19:49:32', NULL),
            (3, 'Pinterest caption', 'Generate a Pinterest caption for a pin based on the following text/keywords: {text}', '{\"translations\":{\"english\":{\"name\":\"Pinterest caption\",\"description\":\"Generate a caption for your pins based on your keywords.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text or keywords to be used\",\"placeholder\":\"\",\"help\":\"\"}}}}}', 'fab fa-pinterest', 24, 0, 1, '2023-05-13 20:40:38', NULL),
            (3, 'TikTok video caption', 'Generate a TikTok video caption based on the following text/keywords: {text}', '{\"translations\":{\"english\":{\"name\":\"TikTok video caption\",\"description\":\"Generate quick & trending captions for your TikTok content with ease.\"}},\"inputs\":{\"text\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Text or keywords to be used\",\"placeholder\":\"\",\"help\":\"\"}}}}}', 'fab fa-tiktok', 25, 0, 1, '2023-05-13 20:42:07', NULL),
            (3, 'TikTok video idea', 'Generate a random TikTok video idea in the following niche: {niche}', '{\"translations\":{\"english\":{\"name\":\"TikTok video idea\",\"description\":\"Generate quick & trending video idea your TikTok account.\"}},\"inputs\":{\"niche\":{\"icon\":\"fas fa-pen\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Niche or Category\",\"placeholder\":\"Breakdance tutorials, Interior design principles, Places to visit in New York\",\"help\":\"Input the niche of the idea that you want to get.\"}}}}}', 'fab fa-tiktok', 26, 0, 1, '2023-05-13 20:55:57', '2023-05-13 21:04:22'),
            (1, 'Song lyrics', 'Generate song lyrics based the following:\r\n\r\nGenre: {genre}\r\n\r\nTopic: {topic}', '{\"translations\":{\"english\":{\"name\":\"Song lyrics\",\"description\":\"Generate high quality lyrics based for any genre.\"}},\"inputs\":{\"topic\":{\"icon\":\"fas fa-pen\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Topic\",\"placeholder\":\"Heartbreak, Love, Motivational, Dynamic\",\"help\":\"Input the topic of the lyrics you wish to generate.\"}}},\"genre\":{\"icon\":\"fas fa-music\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Genre\",\"placeholder\":\"Rap, Hip Hop, Pop, Rock\",\"help\":\"Input the genre of the lyrics you wish to generate.\"}}}}}', 'fas fa-music', 2, 1, 1, '2023-05-13 21:09:05', '2023-05-13 21:12:38'),
            (1, 'Joke generator', 'Generate a random funny joke on the following topic: {topic}', '{\"translations\":{\"english\":{\"name\":\"Joke generator\",\"description\":\"Get random and funny jokes based on the topic you wish.\"}},\"inputs\":{\"topic\":{\"icon\":\"fas fa-pen\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Topic\",\"placeholder\":\"Edgy, Cringe, Modern, Dark humor\",\"help\":\"Input the type of joke you wish to generate.\"}}}}}', 'fas fa-laugh-beam', 2, 0, 1, '2023-05-13 21:17:22', '2023-05-13 21:18:55'),
            (2, 'Welcome email', 'Write a welcome email subject and body &#34;{name}&#34; product/service with the following description: {description}', '{\"translations\":{\"english\":{\"name\":\"Welcome email\",\"description\":\"Generate great engaging emails for your new users.\"}},\"inputs\":{\"name\":{\"icon\":\"fas fa-heading\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Product or service name\",\"placeholder\":\"OpenAI\",\"help\":\"\"}}},\"description\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Description\",\"placeholder\":\"Our web platform helps users get start with AI with ease.\",\"help\":\"\"}}}}}', 'fas fa-envelope-open', 23, 1, 1, '2023-05-14 09:54:39', '2023-05-14 10:59:45'),
            (2, 'Outreach email', 'Write a cold outreach email subject and body &#34;{name}&#34; product/service with the following description: {description}', '{\"translations\":{\"english\":{\"name\":\"Outreach email\",\"description\":\"Generate great emails for cold outreach to get more leads.\"}},\"inputs\":{\"name\":{\"icon\":\"fas fa-heading\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Product or service name\",\"placeholder\":\"OpenAI\",\"help\":\"\"}}},\"description\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Description\",\"placeholder\":\"Our web platform helps users get start with AI with ease.\",\"help\":\"\"}}}}}', 'fas fa-envelope', 24, 0, 1, '2023-05-14 10:56:37', '2023-05-14 10:59:51'),
            (2, 'Facebook advertisement', 'Generate a Facebook ad copy for the &#34;{name}&#34; product/service with the following description: {description}', '{\"translations\":{\"english\":{\"name\":\"Facebook advertisement\",\"description\":\"Generate Facebook optimized ad copy details for a product or service.\"}},\"inputs\":{\"name\":{\"icon\":\"fas fa-heading\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Product or service name\",\"placeholder\":\"Booking.com\",\"help\":\"\"}}},\"description\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Description\",\"placeholder\":\"The largest and most trusted online booking and traveling agencies.\",\"help\":\"\"}}}}}', 'fab fa-facebook', 25, 0, 1, '2023-05-14 11:29:22', '2023-05-14 11:39:04'),
            (2, 'Google advertisement', 'Generate a Google ad copy for the &#34;{name}&#34; product/service with the following description: {description}', '{\"translations\":{\"english\":{\"name\":\"Google advertisement\",\"description\":\"Generate Google optimized ad copy details for a product or service.\"}},\"inputs\":{\"name\":{\"icon\":\"fas fa-heading\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Product or service name\",\"placeholder\":\"Booking.com\",\"help\":\"\"}}},\"description\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Description\",\"placeholder\":\"The largest and most trusted online booking and traveling agencies.\",\"help\":\"\"}}}}}', 'fab fa-google', 26, 0, 1, '2023-05-14 11:39:14', '2023-05-14 11:39:51'),
            (2, 'LinkedIn advertisement', 'Generate a LinkedIn ad copy for the &#34;{name}&#34; product/service with the following description: {description}', '{\"translations\":{\"english\":{\"name\":\"LinkedIn advertisement\",\"description\":\"Generate LinkedIn optimized ad copy details for a product or service.\"}},\"inputs\":{\"name\":{\"icon\":\"fas fa-heading\",\"type\":\"input\",\"translations\":{\"english\":{\"name\":\"Product or service name\",\"placeholder\":\"Booking.com\",\"help\":\"\"}}},\"description\":{\"icon\":\"fas fa-paragraph\",\"type\":\"textarea\",\"translations\":{\"english\":{\"name\":\"Description\",\"placeholder\":\"The largest and most trusted online booking and traveling agencies.\",\"help\":\"\"}}}}}', 'fab fa-linkedin', 27, 0, 1, '2023-05-14 11:40:12', '2023-05-14 11:40:37');
            ",

            "CREATE TABLE `documents` (
            `document_id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `user_id` {$user_id_type} DEFAULT NULL,
            `project_id` {$user_id_type} DEFAULT NULL,
            `template_id` bigint unsigned DEFAULT NULL,
            `template_category_id` bigint unsigned DEFAULT NULL,
            `name` varchar(64) DEFAULT NULL,
            `type` varchar(32) DEFAULT NULL,
            `input` text,
            `content` text,
            `words` int unsigned DEFAULT NULL,
            `settings` text,
            `model` varchar(64) DEFAULT NULL,
            `api_response_time` int unsigned DEFAULT NULL,
            `datetime` datetime DEFAULT NULL,
            `last_datetime` datetime DEFAULT NULL,
            PRIMARY KEY (`document_id`),
            KEY `user_id` (`user_id`),
            KEY `project_id` (`project_id`),
            KEY `documents_templates_template_id_fk` (`template_id`),
            KEY `documents_templates_categories_template_category_id_fk` (`template_category_id`),
            CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `documents_templates_categories_template_category_id_fk` FOREIGN KEY (`template_category_id`) REFERENCES `templates_categories` (`template_category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `documents_templates_template_id_fk` FOREIGN KEY (`template_id`) REFERENCES `templates` (`template_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "CREATE TABLE `images` (
            `image_id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `user_id` {$user_id_type} DEFAULT NULL,
            `project_id` {$user_id_type} DEFAULT NULL,
            `variants_ids` text,
            `name` varchar(64) DEFAULT NULL,
            `input` text,
            `image` varchar(40) DEFAULT NULL,
            `style` varchar(128) DEFAULT NULL,
            `artist` varchar(128) DEFAULT NULL,
            `lighting` varchar(128) DEFAULT NULL,
            `mood` varchar(128) DEFAULT NULL,
            `size` varchar(16) DEFAULT NULL,
            `settings` text,
            `api` varchar(64) DEFAULT NULL,
            `api_response_time` int unsigned DEFAULT NULL,
            `datetime` datetime DEFAULT NULL,
            `last_datetime` datetime DEFAULT NULL,
            PRIMARY KEY (`image_id`),
            KEY `user_id` (`user_id`),
            KEY `project_id` (`project_id`),
            CONSTRAINT `images_projects_project_id_fk` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `images_users_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "CREATE TABLE `transcriptions` (
            `transcription_id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `user_id` {$user_id_type} DEFAULT NULL,
            `project_id` {$user_id_type} DEFAULT NULL,
            `name` varchar(64) DEFAULT NULL,
            `input` text,
            `content` text,
            `words` int unsigned DEFAULT NULL,
            `language` varchar(32) DEFAULT NULL,
            `settings` text,
            `api_response_time` int unsigned DEFAULT NULL,
            `datetime` datetime DEFAULT NULL,
            `last_datetime` datetime DEFAULT NULL,
            PRIMARY KEY (`transcription_id`),
            KEY `user_id` (`user_id`),
            KEY `project_id` (`project_id`),
            CONSTRAINT `transcriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `transcriptions_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "CREATE TABLE `chats_assistants` (
            `chat_assistant_id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `prompt` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `settings` text COLLATE utf8mb4_unicode_ci,
            `image` varchar(404) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `order` int DEFAULT NULL,
            `total_usage` bigint unsigned DEFAULT '0',
            `is_enabled` tinyint unsigned DEFAULT '1',
            `last_datetime` datetime DEFAULT NULL,
            `datetime` datetime DEFAULT NULL,
            PRIMARY KEY (`chat_assistant_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "CREATE TABLE `chats` (
            `chat_id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `user_id` {$user_id_type} DEFAULT NULL,
            `chat_assistant_id` bigint unsigned DEFAULT NULL,
            `name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `settings` text COLLATE utf8mb4_unicode_ci,
            `total_messages` int unsigned DEFAULT '0',
            `used_tokens` int unsigned DEFAULT '0',
            `datetime` datetime DEFAULT NULL,
            `last_datetime` datetime DEFAULT NULL,
            PRIMARY KEY (`chat_id`),
            KEY `user_id` (`user_id`),
            KEY `chats_chats_assistants_chat_assistant_id_fk` (`chat_assistant_id`),
            CONSTRAINT `chats_chats_assistants_chat_assistant_id_fk` FOREIGN KEY (`chat_assistant_id`) REFERENCES `chats_assistants` (`chat_assistant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `chats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "CREATE TABLE `chats_messages` (
            `chat_message_id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `chat_id` bigint unsigned DEFAULT NULL,
            `user_id` {$user_id_type} DEFAULT NULL,
            `role` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `content` text COLLATE utf8mb4_unicode_ci,
            `image` varchar(40) DEFAULT NULL,
            `model` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `api_response_time` int unsigned DEFAULT NULL,
            `datetime` datetime DEFAULT NULL,
            PRIMARY KEY (`chat_message_id`),
            KEY `chat_id` (`chat_id`),
            KEY `user_id` (`user_id`),
            CONSTRAINT `chats_messages_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`chat_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `chats_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "INSERT INTO `chats_assistants` (`chat_assistant_id`, `name`, `prompt`, `settings`, `image`, `order`, `total_usage`, `is_enabled`, `last_datetime`, `datetime`) VALUES (1, 'General Assistant', 'You are a general assistant that can help with anything.', '{\"translations\":{\"english\":{\"name\":\"General Assistant\",\"description\":\"I can help you with any general task or question.\"}}}', 'de618ff8b13d6aa0b7df3b91b16cb452.png', 0, 0, 1, null, NOW());",

            "CREATE TABLE `syntheses` (
            `synthesis_id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `user_id` {$user_id_type} DEFAULT NULL,
            `project_id` {$user_id_type} DEFAULT NULL,
            `name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `input` text COLLATE utf8mb4_unicode_ci,
            `file` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `language` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `voice_id` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `voice_engine` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `voice_gender` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `settings` text COLLATE utf8mb4_unicode_ci,
            `characters` int unsigned DEFAULT '0',
            `api_response_time` int unsigned DEFAULT NULL,
            `datetime` datetime DEFAULT NULL,
            `last_datetime` datetime DEFAULT NULL,
            PRIMARY KEY (`synthesis_id`),
            KEY `user_id` (`user_id`),
            KEY `project_id` (`project_id`),
            CONSTRAINT `syntheses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `syntheses_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        ];

        foreach($queries as $query) {
            database()->query($query);
        }

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItem('settings');

        return Plugin::save_status(self::$plugin_id, 'active');

    }

    public static function uninstall() {

        /* Run the installation process of the plugin */
        $queries = [
            "DELETE FROM `settings` WHERE `key` = 'aix';",
            "DELETE FROM `settings` WHERE `key` = 'ai_writer';",
            "alter table `users` drop ai_writer_words_current_month;",
            "alter table `users` drop aix_documents_current_month;",
            "alter table `users` drop aix_words_current_month;",
            "alter table `users` drop aix_images_current_month;",
            "alter table `users` drop aix_transcriptions_current_month;",
            "alter table `users` drop aix_chats_current_month;",
            "drop table `documents`",
            "drop table `images`",
            "drop table `transcriptions`",
            "drop table `chats_messages`",
            "drop table `chats`",
            "drop table `chats_assistants`",
            "drop table `templates`",
            "drop table `templates_categories`",
        ];

        foreach($queries as $query) {
            try {
                database()->query($query);
            } catch (\Exception $exception) {
                // :)
            }
        }

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItem('settings');

        return Plugin::save_status(self::$plugin_id, 'uninstalled');

    }

    public static function activate() {
        return Plugin::save_status(self::$plugin_id, 'active');
    }

    public static function disable() {
        return Plugin::save_status(self::$plugin_id, 'installed');
    }

}
