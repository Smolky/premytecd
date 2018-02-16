import java.io.IOException;
import java.nio.charset.Charset;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Paths;

/**
 * Main
 * 
 * This is just a sample app to test the application
 * 
 * @package PreMyTECD
 */
public class Main {
	
	/**
	 * main
	 * 
	 */
	public static void main (String[] args) {
		
		CDAExport cdaexport = new CDAExport ();
		
		try {
			String historial_raw = Main.readFile ("sample.json", StandardCharsets.ISO_8859_1);
			System.out.println(cdaexport.render (historial_raw));
			
		} catch (IOException e) {
			System.err.println("Operation error");
		}
		
	}
	
	
	/**
	 * readFile
	 * 
	 * @param path
	 * @param encoding
	 * 
	 * @see https://stackoverflow.com/questions/326390/how-do-i-create-a-java-string-from-the-contents-of-a-file
	 * 
	 * @return
	 * @throws IOException
	 */
	public static String readFile(String path, Charset encoding) throws IOException {
		byte[] encoded = Files.readAllBytes(Paths.get(path));
		return new String(encoded, encoding);
	}	
}
