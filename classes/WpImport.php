<?php namespace Tiipiik\Import\Classes;

/*
 * Based on Zac Vineyard
 * https://github.com/zvineyard/pyrocms-import-export/blob/master/libraries/Wp_import.php
 *
 */

class WpImport {

	private $ci;
	
	function __construct()
	{
        $this->ci =& get_instance();
    }
	
	private function get_duplicates($array)
	{
		return array_unique(array_diff_assoc($array, array_unique($array)));
	}

	public static function has_duplicate_titles($xml)
	{

		$titles = array();
		foreach ($xml->channel->item as $val)
		{
			if((string) $val->content != "" && (string) $val->post_type == "post" && (string) $val->status == 'publish')
			{
				$titles[] = strtolower((string) mb_convert_encoding($val->title,"HTML-ENTITIES", "UTF-8"));
			}
		}
		$dups = $this->get_duplicates($titles);
		if(count($dups) > 0)
		{
			return $dups;
		}
		else
		{
			return false;
		}
		
	}
	
	public static function categories($xml)
	{
		$categories = [];
		foreach ($xml->channel->category as $val)
		{
			$categories[] = array(
				'slug' => (string) $val->category_nicename,
				'title' => (string) $val->cat_name
			);
		}
		
		return $categories;
	}
	
	public static function tags($xml)
	{
	
		foreach ($xml->channel->tag as $val)
		{
			$tags[] = array(
				'name' => (string) $val->tag_name
			);
		}
		if(!empty($tags))
		{
			if($this->ci->db->insert_batch('keywords', $tags))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	
	}
	
	public static function posts($xml)
	{
		// Defaults
		$posts = array();
		
		foreach ($xml->channel->item as $val) {
		
			$slug = (string) $val->post_name;
						
			$comments_enabled = ($val->comment_status == 'open') ? '3 months' : 'no';
			
			$status = ($val->status === 'publish') ? 'draft' : 'live';
			
			// Get content, category, and tags for every post
			if((string) $val->content != "" && (string) $val->post_type == "post" && (string) $val->status == "publish")
			{
				
				// Get a category slug
				$category_slug = "";
				foreach($val->category as $cat)
				{
					$category_slug = "";
					if($cat[0]['domain'] == 'category')
					{
						$category_slug = (string) $cat[0]['nicename'];
						break;
					}
				}
				
				// Query the ID of this posts's category slug
				$category_id = 0;
				/*
				if($category_slug != "")
				{
					$this->ci->db->where('slug',$category_slug);
					$this->ci->db->limit(1);
					$query = $this->ci->db->get('blog_categories');
					if($query->num_rows() > 0)
					{
						foreach ($query->result() as $row)
						{
							$category_id = $row->id;
						}
					}
				}
				*/
				
				// Get tag slugs
				$tag_slugs = array();
				foreach($val->category as $tag)
				{
					if($tag[0]['domain'] == 'post_tag')
					{
						$tag_slugs[] = (string) $tag;
					}
				}
				
				$posts[] = array(
					'title' => (string) $val->title,
					'slug' => $slug,
					'category_id' => $category_id,
					'created' => (string) $val->post_date,
					'intro' => (string) mb_convert_encoding($val->excerpt,"HTML-ENTITIES", "UTF-8"),
					'body' => nl2br((string) mb_convert_encoding($val->content,"HTML-ENTITIES", "UTF-8")),
					'parsed' => '',
					'author_id' => $val->author_id,
					'created_on' => (string) strtotime($val->post_date),
					'updated_on' => (string) strtotime($val->pubDate),
					'comments_enabled' => (string) (!$comments_enabled) ? "no" : "always",
					'status' => $status,
					'type' => 'wysiwyg-advanced'
				);
			}
		}

		return $posts;
	}
	
	public static function comments($xml)
	{
	
		foreach ($xml->channel->item as $val)
		{			
			$slug = (string) $val->post_name;
			
			// Comments
			if($val->comment)
			{
				foreach($val->comment as $comment)
				{
					if($comment->comment_type == "")
					{	
						$comments[$slug][] = array(
							'is_active' => 1,
							'user_id' => 0,
							'name' => (string) $comment->comment_author,
							'email' => (string) $comment->comment_author_email,
							'website' => (string) $comment->comment_author_url,
							'comment' => (string) mb_convert_encoding($comment->comment_content,"HTML-ENTITIES","UTF-8"),
							'parsed' => '',
							'module' => 'blog',
							//'module_id' => 1, // ID of the post/page
							'created_on' => (string) strtotime($comment->comment_date),
							'ip_address' => (string) $comment->comment_author_IP
						);
					}
				}
			}						
		}
		
		// Now that you have a comments array you can query all posts, and for each post, batch add comments (I know this hurts)
		$query = $this->ci->db->get('blog');
		if($query->num_rows() > 0) {
			foreach ($query->result() as $row)
			{
				if(isset($comments[$row->slug]))
				{
					$counter = 0;
					foreach($comments[$row->slug] as $v)
					{
					//for($i = 0; $i <= count($comments[$row->slug]); $i++) { // getting a memory error here
						$comments[$row->slug][$counter]['module_id'] = $row->id;
						$counter++;
					}	
					$this->ci->db->insert_batch('comments', $comments[$row->slug]);				
				}				
			}
		}
		
	} // end comments method
	
	public static function users($xml)
	{
		
		// Move this function to a helper
		function randString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
		{
			$str = '';
			$count = strlen($charset);
			while ($length--)
			{
				$str .= $charset[mt_rand(0, $count-1)];
			}
			return $str;
		}
	
		foreach ($xml->channel->author as $val)
		{
			$rand = randString(6);
			$user = array(
				'email' => (string) $val->author_email,
				'password' => md5((string)$val->author_email.$rand.time()),
				'salt' => $rand,
				'group_id' => 1,
				'active' => 1,
				'created_on' => time(),
				'last_login' => 0,
				'username' => (string) $val->author_login
			);
			$this->ci->db->where('username',(string) $val->author_login);
			$this->ci->db->or_where('email',(string) $val->author_email);
			$query = $this->ci->db->get('users');
			if($query->num_rows() == 0)
			{
				$this->ci->db->insert('users',$user);
				$user_id = $this->ci->db->insert_id();
				$profile = array(
					'user_id' => $user_id,
					'display_name' => (string) $val->author_display_name,
					'first_name' => '[first_name]',
					'last_name' => '[last_name]',
					'lang' => 'en'
				);
				$this->ci->db->insert('profiles',$profile);
			}
		}
	
	}

	public static function pages($xml)
	{
		// Defaults
		$parent_pages = array();
		$child_pages = array();
		$parents = array(); // key = post id, val = parent id

		foreach ($xml->channel->item as $val)
		{
			$parent_id = (string) $val->post_parent;
			if($parent_id != 0)
			{
				$parents[(string) $val->post_id] = $parent_id;
			}
		}
		
		foreach ($xml->channel->item as $val)
		{
		
			$slug = (string) $val->post_name;
						
			$comments_enabled = ((string) $val->comment_status == 'open') ? 1 : 0;
			
			$status = ((string) $val->status == 'publish') ? 'live' : 'draft';

			// Get page content and other misc values
			if((string) $val->content != "" && (string) $val->post_type == "page" && (string) $val->status == "publish")
			{

				$pages[] = array(
					'title' => (string) $val->title,
					'slug' => $slug,
					'uri' => $slug,
					'parent_id' => 0,
					'revision_id' => 1,
					'layout_id' => 1,
					'meta_title' => '',
					'meta_keywords' => '',
					'meta_description' => '',
					'comments_enabled' => $comments_enabled,
					'status' => $status,
					'created_on' => (string) strtotime($val->post_date),
					'updated_on' => (string) strtotime($val->pubDate),
					'is_home' => 0,
					'strict_uri' => 1,
					'order' => 0,
					'html' => nl2br((string) mb_convert_encoding($val->content,"HTML-ENTITIES","UTF-8"))
				);

			}
			
		}
		
		return $pages;

		foreach($pages as $page)
		{
			$html = $page['html'];
			unset($page['html']);
			/*
			self->ci->db->insert('pages',$page);
			$chunk = array(
				'slug' => SITE_REF,
				'class' => '',
				'page_id' => $this->ci->db->insert_id(),
				'body' => $html,
				'type' => 'html',
				'parsed' => '',
				'sort' => 1
			);
			$this->ci->db->insert('page_chunks',$chunk);
			*/
		}

	}
    
    /*
     * From Zac Vineyard
     * https://github.com/zvineyard/pyrocms-import-export/blob/master/controllers/admin.php
     *
     */
    public static function get_filtered_wp_xml($file)
    {
        //$xml = file_get_contents('uploads/'.SITE_REF.'/import_export/'.$file);
        $xml = file_get_contents($file);
        
        return simplexml_load_string(str_replace(array(
            'content:encoded',
            'excerpt:encoded',
            'wp:',
        ), array(
            'content',
            'excerpt',
            '',
        ), $xml));
    }
			
}
